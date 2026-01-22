<?php
/**
 * Credits Controller
 *
 * Handles credit-related pages and API endpoints:
 * - /progress - Level progress page
 * - /credit - Credit history page
 * - /board - Check-in page
 * - /credits/checkin - Check-in API
 * - /credits/feed - Feed API
 *
 * @author BitsMesh
 * @version 1.0.0
 */

class CreditsController extends Gdn_Controller {

    /** @var array Models to auto-instantiate */
    public $Uses = ['Form'];

    /**
     * Include theme master view.
     *
     * @param string $view
     * @param string $controllerName
     * @param string $applicationFolder
     * @param string $assetName
     */
    public function xRender($view = '', $controllerName = '', $applicationFolder = '', $assetName = 'Content') {
        // Use theme's master view
        $this->MasterView = 'default';
        parent::xRender($view, $controllerName, $applicationFolder, $assetName);
    }

    /**
     * Initialize controller.
     */
    public function initialize() {
        parent::initialize();

        // Require login for all credit pages
        if (!Gdn::session()->isValid()) {
            redirectTo(signInUrl($this->SelfUrl));
        }

        // Add CSS
        $this->addCssFile('credits.css', 'plugins/Credits');
    }

    /**
     * Level progress page - /progress
     */
    public function progress() {
        $this->title(t('Credits.Progress', '等级进度'));
        $this->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.Progress', '等级进度'), 'Url' => '/progress']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        $this->setData('Credits', $credits);
        $this->setData('Level', CreditsPlugin::calculateLevel($credits));
        $this->setData('Progress', CreditsPlugin::getProgressToNextLevel($credits));
        $this->setData('LevelThresholds', CreditsPlugin::LEVEL_THRESHOLDS);

        // Credit rules
        $this->setData('CreditRules', [
            'post' => [
                'name' => t('Credits.PostRule', '发帖'),
                'amount' => '+' . CreditsPlugin::CREDIT_POST,
                'limit' => CreditsPlugin::DAILY_LIMIT_POST,
                'note' => t('Credits.PostRuleNote', '每个帖子')
            ],
            'comment' => [
                'name' => t('Credits.CommentRule', '评论'),
                'amount' => '+' . CreditsPlugin::CREDIT_COMMENT,
                'limit' => CreditsPlugin::DAILY_LIMIT_COMMENT,
                'note' => t('Credits.CommentRuleNote', '每条评论')
            ],
            'checkin' => [
                'name' => t('Credits.CheckInRule', '签到'),
                'amount' => '+' . CreditsPlugin::CREDIT_CHECKIN_MIN . '~' . CreditsPlugin::CREDIT_CHECKIN_MAX,
                'limit' => CreditsPlugin::CREDIT_CHECKIN_MAX,
                'note' => t('Credits.CheckInRuleNote', '随机获得')
            ],
            'feed' => [
                'name' => t('Credits.FeedRule', '被投喂'),
                'amount' => t('Credits.Unlimited', '不限'),
                'limit' => null,
                'note' => t('Credits.FeedRuleNote', '其他用户赠送')
            ]
        ]);

        $this->render('progress', '', 'plugins/Credits');
    }

    /**
     * Credit history page - /credit
     */
    public function credit($page = '') {
        $this->title(t('Credits.CreditHistory', '鸡腿账簿'));
        $this->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.CreditHistory', '鸡腿账簿'), 'Url' => '/credit']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        // Parse page number from URL like /credit#/p-1
        $pageNumber = 1;
        if (preg_match('/^p-?(\d+)$/i', $page, $matches)) {
            $pageNumber = (int)$matches[1];
        }
        $pageNumber = max(1, $pageNumber);

        $limit = 20;
        $offset = ($pageNumber - 1) * $limit;

        $plugin = Gdn::pluginManager()->getPluginInstance('Credits', Gdn_PluginManager::ACCESS_CLASSNAME);

        $this->setData('Credits', $credits);
        $this->setData('Level', CreditsPlugin::calculateLevel($credits));
        $this->setData('CreditLog', $plugin->getCreditLog($userID, $limit, $offset));
        $this->setData('TotalCount', $plugin->getCreditLogCount($userID));
        $this->setData('Page', $pageNumber);
        $this->setData('PageSize', $limit);
        $this->setData('TotalPages', ceil($plugin->getCreditLogCount($userID) / $limit));

        $this->render('credit', '', 'plugins/Credits');
    }

    /**
     * Check-in page - /board
     */
    public function board() {
        $this->title(t('Credits.CheckIn', '每日签到'));
        $this->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.CheckIn', '每日签到'), 'Url' => '/board']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        $plugin = Gdn::pluginManager()->getPluginInstance('Credits', Gdn_PluginManager::ACCESS_CLASSNAME);

        $this->setData('Credits', $credits);
        $this->setData('Level', CreditsPlugin::calculateLevel($credits));
        $this->setData('CanCheckIn', $plugin->canCheckIn($userID));
        $this->setData('ConsecutiveDays', $plugin->getConsecutiveCheckInDays($userID));
        $this->setData('LastCheckIn', $plugin->getLastCheckInDate($userID));

        // Get calendar data
        $year = date('Y');
        $month = date('m');
        $this->setData('CalendarYear', $year);
        $this->setData('CalendarMonth', $month);
        $this->setData('CheckedDays', $plugin->getCheckInCalendar($userID, $year, $month));

        $this->render('board', '', 'plugins/Credits');
    }

    /**
     * Check-in API - POST /credits/checkin
     */
    public function checkIn() {
        $this->permission('Garden.SignIn.Allow');

        if (!$this->Form->authenticatedPostBack(true)) {
            throw new Gdn_UserException(t('Invalid CSRF token'), 403);
        }

        $userID = Gdn::session()->UserID;
        $plugin = Gdn::pluginManager()->getPluginInstance('Credits', Gdn_PluginManager::ACCESS_CLASSNAME);

        $result = $plugin->doCheckIn($userID);

        $this->setData('Success', $result['success']);
        $this->setData('Amount', $result['amount']);
        $this->setData('Consecutive', $result['consecutive']);
        $this->setData('Message', $result['message']);

        // Get updated credits
        $user = Gdn::userModel()->getID($userID);
        $this->setData('Credits', val('Points', $user, 0));

        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Feed API - POST /credits/feed
     */
    public function feed() {
        $this->permission('Garden.SignIn.Allow');

        if (!$this->Form->authenticatedPostBack(true)) {
            throw new Gdn_UserException(t('Invalid CSRF token'), 403);
        }

        $userID = Gdn::session()->UserID;
        $toUserID = $this->Form->getFormValue('ToUserID');
        $amount = (int)$this->Form->getFormValue('Amount', 1);
        $discussionID = (int)$this->Form->getFormValue('DiscussionID', 0);

        if (!$toUserID) {
            throw new Gdn_UserException(t('Credits.InvalidUser', '目标用户无效'), 400);
        }

        $plugin = Gdn::pluginManager()->getPluginInstance('Credits', Gdn_PluginManager::ACCESS_CLASSNAME);
        $result = $plugin->doFeed($userID, $toUserID, $amount, $discussionID);

        $this->setData('Success', $result['success']);
        $this->setData('Message', $result['message']);

        // Get updated credits
        $user = Gdn::userModel()->getID($userID);
        $this->setData('Credits', val('Points', $user, 0));
        $this->setData('CanFeed', $plugin->canFeed($userID));

        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Get type label for display.
     *
     * @param string $type
     * @return string
     */
    public static function getTypeLabel($type) {
        $labels = [
            'post' => t('Credits.TypePost', '发帖奖励'),
            'comment' => t('Credits.TypeComment', '评论奖励'),
            'checkin' => t('Credits.TypeCheckIn', '签到奖励'),
            'feed_give' => t('Credits.TypeFeedGive', '投喂他人'),
            'feed_receive' => t('Credits.TypeFeedReceive', '收到投喂')
        ];
        return $labels[$type] ?? $type;
    }
}
