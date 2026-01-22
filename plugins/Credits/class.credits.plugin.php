<?php
/**
 * Credits System Plugin
 *
 * BitsMesh Credits (Chicken Legs) and Level System
 * Provides:
 * - Credit earning from posts, comments, check-in
 * - 6-level user ranking system (Lv1-Lv6)
 * - Credit transaction logging
 * - Feed functionality for giving credits to others
 *
 * @author BitsMesh
 * @version 1.0.0
 * @license GPL-2.0-only
 */

/**
 * Credits Plugin Class
 */
class CreditsPlugin extends Gdn_Plugin {

    /** Level thresholds */
    const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 1000,
        4 => 2500,
        5 => 5000,
        6 => 10000
    ];

    /** Credit amounts */
    const CREDIT_POST = 5;
    const CREDIT_COMMENT = 1;
    const CREDIT_CHECKIN_MIN = 1;
    const CREDIT_CHECKIN_MAX = 20;

    /** Daily limits */
    const DAILY_LIMIT_POST = 20;
    const DAILY_LIMIT_COMMENT = 20;
    const DAILY_FEED_QUOTA = 1;

    /**
     * Plugin setup - create database tables and roles.
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Database structure.
     */
    public function structure() {
        $construct = Gdn::structure();

        // Create GDN_CreditLog table
        $construct->table('CreditLog')
            ->primaryKey('LogID')
            ->column('UserID', 'int', false, 'index.user_date')
            ->column('Amount', 'int', false)
            ->column('Balance', 'int', false)
            ->column('Type', 'varchar(20)', false, 'index')
            ->column('RelatedID', 'int', true)
            ->column('RelatedUserID', 'int', true)
            ->column('Note', 'varchar(255)', true)
            ->column('DateInserted', 'datetime', false, 'index.user_date')
            ->set(false, false);

        // Add level roles (Lv1-Lv6)
        $this->setupLevelRoles();
    }

    /**
     * Setup level roles Lv1-Lv6.
     */
    protected function setupLevelRoles() {
        $sql = Gdn::sql();

        // Check if roles already exist
        $existingRoles = $sql->select('RoleID, Name')
            ->from('Role')
            ->whereIn('Name', ['Lv1', 'Lv2', 'Lv3', 'Lv4', 'Lv5', 'Lv6'])
            ->get()
            ->resultArray();

        if (count($existingRoles) >= 6) {
            // Roles exist, ensure config is saved
            $roleIDs = [];
            foreach ($existingRoles as $role) {
                $level = (int)substr($role['Name'], 2); // Extract level number from "Lv1", "Lv2", etc.
                $roleIDs[$level] = (int)$role['RoleID'];
            }
            ksort($roleIDs);

            // Save to config if not already set
            if (!c('Credits.LevelRoleIDs')) {
                saveToConfig('Credits.LevelRoleIDs', $roleIDs);
            }
            return;
        }

        // Create missing roles
        $maxRoleID = $sql->select('RoleID', 'max')
            ->from('Role')
            ->get()
            ->value('RoleID', 0);

        $baseRoleID = max($maxRoleID + 1, 64);

        $levelRoles = [
            ['RoleID' => $baseRoleID,     'Name' => 'Lv1', 'Description' => '一级会员', 'Type' => 'member', 'Sort' => 10],
            ['RoleID' => $baseRoleID + 1, 'Name' => 'Lv2', 'Description' => '二级会员', 'Type' => 'member', 'Sort' => 11],
            ['RoleID' => $baseRoleID + 2, 'Name' => 'Lv3', 'Description' => '三级会员', 'Type' => 'member', 'Sort' => 12],
            ['RoleID' => $baseRoleID + 3, 'Name' => 'Lv4', 'Description' => '四级会员', 'Type' => 'member', 'Sort' => 13],
            ['RoleID' => $baseRoleID + 4, 'Name' => 'Lv5', 'Description' => '五级会员', 'Type' => 'member', 'Sort' => 14],
            ['RoleID' => $baseRoleID + 5, 'Name' => 'Lv6', 'Description' => '六级会员', 'Type' => 'member', 'Sort' => 15],
        ];

        foreach ($levelRoles as $role) {
            $sql->insert('Role', array_merge($role, [
                'Deletable' => 0,
                'CanSession' => 1,
                'PersonalInfo' => 0
            ]));
        }

        // Save level role IDs to config
        saveToConfig('Credits.LevelRoleIDs', [
            1 => $baseRoleID,
            2 => $baseRoleID + 1,
            3 => $baseRoleID + 2,
            4 => $baseRoleID + 3,
            5 => $baseRoleID + 4,
            6 => $baseRoleID + 5,
        ]);
    }

    /**
     * Calculate user level from credits.
     *
     * @param int $credits
     * @return int Level 1-6
     */
    public static function calculateLevel($credits) {
        $level = 1;
        foreach (self::LEVEL_THRESHOLDS as $lv => $threshold) {
            if ($credits >= $threshold) {
                $level = $lv;
            }
        }
        return $level;
    }

    /**
     * Get progress to next level.
     *
     * @param int $credits
     * @return array ['percentage', 'needed', 'nextLevel', 'currentThreshold', 'nextThreshold']
     */
    public static function getProgressToNextLevel($credits) {
        $currentLevel = self::calculateLevel($credits);

        if ($currentLevel >= 6) {
            return [
                'percentage' => 100,
                'needed' => 0,
                'nextLevel' => 6,
                'currentThreshold' => self::LEVEL_THRESHOLDS[6],
                'nextThreshold' => self::LEVEL_THRESHOLDS[6]
            ];
        }

        $currentThreshold = self::LEVEL_THRESHOLDS[$currentLevel];
        $nextThreshold = self::LEVEL_THRESHOLDS[$currentLevel + 1];
        $range = $nextThreshold - $currentThreshold;
        $progress = $credits - $currentThreshold;

        return [
            'percentage' => round(($progress / $range) * 100, 1),
            'needed' => $nextThreshold - $credits,
            'nextLevel' => $currentLevel + 1,
            'currentThreshold' => $currentThreshold,
            'nextThreshold' => $nextThreshold
        ];
    }

    /**
     * Register routes.
     *
     * @param Gdn_Dispatcher $sender
     */
    public function gdn_dispatcher_beforeDispatch_handler($sender) {
        // Route /progress, /credit, /board to PluginController
        $request = $sender->EventArguments['Request'];
        $path = $request->path();

        if (preg_match('`^progress(/.*)?$`i', $path)) {
            $request->path('plugin/progress');
        } elseif (preg_match('`^credit(/p(\d+))?$`i', $path, $matches)) {
            $page = isset($matches[2]) ? (int)$matches[2] : '';
            $newPath = 'plugin/credit';
            if ($page > 0) {
                $newPath .= '/p' . $page;
            }
            $request->path($newPath);
        } elseif (preg_match('`^board(/.*)?$`i', $path)) {
            $request->path('plugin/board');
        }
    }

    /**
     * Award credits for new discussion.
     *
     * @param DiscussionModel $sender
     * @param array $args
     */
    public function discussionModel_afterSaveDiscussion_handler($sender, $args) {
        // Only award for new discussions, not edits
        if (!val('Insert', $args)) {
            return;
        }

        $discussionID = val('DiscussionID', $args);
        $discussion = val('Discussion', $args, []);
        $userID = val('InsertUserID', $discussion);

        if (!$userID || !$discussionID) {
            return;
        }

        $this->awardCredits($userID, self::CREDIT_POST, 'post', $discussionID);
    }

    /**
     * Award credits for new comment.
     *
     * @param CommentModel $sender
     * @param array $args
     */
    public function commentModel_afterSaveComment_handler($sender, $args) {
        // Only award for new comments, not edits
        if (!val('Insert', $args)) {
            return;
        }

        $commentID = val('CommentID', $args);
        $comment = val('Comment', $args, []);
        $userID = val('InsertUserID', $comment);

        if (!$userID || !$commentID) {
            return;
        }

        $this->awardCredits($userID, self::CREDIT_COMMENT, 'comment', $commentID);
    }

    /**
     * Award credits to user with daily limit check.
     *
     * @param int $userID
     * @param int $amount
     * @param string $type post|comment|checkin|feed_give|feed_receive
     * @param int|null $relatedID
     * @param int|null $relatedUserID
     * @param string|null $note
     * @return bool Whether credits were awarded
     */
    public function awardCredits($userID, $amount, $type, $relatedID = null, $relatedUserID = null, $note = null) {
        // Check daily limit for post/comment
        if (in_array($type, ['post', 'comment'])) {
            $today = date('Ymd');
            $metaKey = 'Credits.Daily' . ucfirst($type) . 'Credits.' . $today;
            $dailyEarned = $this->getUserDailyCredits($userID, $type);
            $limit = $type === 'post' ? self::DAILY_LIMIT_POST : self::DAILY_LIMIT_COMMENT;

            if ($dailyEarned >= $limit) {
                return false; // Daily limit reached
            }

            // Update daily earned
            Gdn::userMetaModel()->setUserMeta($userID, $metaKey, $dailyEarned + $amount);
        }

        // Get current balance
        $user = Gdn::userModel()->getID($userID);
        $currentBalance = val('Points', $user, 0);
        $newBalance = $currentBalance + $amount;

        // Update user points
        Gdn::userModel()->setField($userID, 'Points', $newBalance);

        // Log transaction
        $this->logTransaction($userID, $amount, $newBalance, $type, $relatedID, $relatedUserID, $note);

        // Check and update level
        $this->updateUserLevel($userID, $newBalance);

        return true;
    }

    /**
     * Get user's daily earned credits.
     *
     * @param int $userID
     * @param string $type post|comment
     * @return int
     */
    public function getUserDailyCredits($userID, $type) {
        $today = date('Ymd');
        $metaKey = 'Credits.Daily' . ucfirst($type) . 'Credits.' . $today;
        $meta = Gdn::userMetaModel()->getUserMeta($userID, $metaKey, 0);
        return (int)(reset($meta) ?: 0);
    }

    /**
     * Log credit transaction.
     *
     * @param int $userID
     * @param int $amount
     * @param int $balance
     * @param string $type
     * @param int|null $relatedID
     * @param int|null $relatedUserID
     * @param string|null $note
     */
    protected function logTransaction($userID, $amount, $balance, $type, $relatedID = null, $relatedUserID = null, $note = null) {
        Gdn::sql()->insert('CreditLog', [
            'UserID' => $userID,
            'Amount' => $amount,
            'Balance' => $balance,
            'Type' => $type,
            'RelatedID' => $relatedID,
            'RelatedUserID' => $relatedUserID,
            'Note' => $note,
            'DateInserted' => Gdn_Format::toDateTime()
        ]);
    }

    /**
     * Update user's level role if needed.
     *
     * @param int $userID
     * @param int $credits
     */
    protected function updateUserLevel($userID, $credits) {
        $newLevel = self::calculateLevel($credits);
        $levelRoleIDs = c('Credits.LevelRoleIDs', []);

        if (empty($levelRoleIDs)) {
            return;
        }

        // Get user's current roles
        $userModel = Gdn::userModel();
        $currentRoles = $userModel->getRoles($userID)->resultArray();
        $currentRoleIDs = array_column($currentRoles, 'RoleID');

        // Find current level role
        $currentLevelRole = null;
        foreach ($levelRoleIDs as $level => $roleID) {
            if (in_array($roleID, $currentRoleIDs)) {
                $currentLevelRole = $level;
                break;
            }
        }

        // If level changed, update role
        if ($currentLevelRole !== $newLevel) {
            // Remove old level roles
            $rolesToRemove = array_intersect($currentRoleIDs, array_values($levelRoleIDs));
            foreach ($rolesToRemove as $roleID) {
                Gdn::sql()->delete('UserRole', ['UserID' => $userID, 'RoleID' => $roleID]);
            }

            // Add new level role
            if (isset($levelRoleIDs[$newLevel])) {
                Gdn::sql()->insert('UserRole', [
                    'UserID' => $userID,
                    'RoleID' => $levelRoleIDs[$newLevel]
                ]);
            }

            // Clear role cache
            $userModel->clearCache($userID);
        }
    }

    /**
     * Check if user can check in today.
     *
     * @param int $userID
     * @return bool
     */
    public function canCheckIn($userID) {
        $lastCheckIn = $this->getLastCheckInDate($userID);
        return $lastCheckIn !== date('Y-m-d');
    }

    /**
     * Get user's last check-in date.
     *
     * @param int $userID
     * @return string|null
     */
    public function getLastCheckInDate($userID) {
        $meta = Gdn::userMetaModel()->getUserMeta($userID, 'Credits.LastCheckIn', null);
        return reset($meta) ?: null;
    }

    /**
     * Get user's consecutive check-in days.
     *
     * @param int $userID
     * @return int
     */
    public function getConsecutiveCheckInDays($userID) {
        $meta = Gdn::userMetaModel()->getUserMeta($userID, 'Credits.ConsecutiveCheckIn', 0);
        return (int)(reset($meta) ?: 0);
    }

    /**
     * Perform check-in.
     *
     * @param int $userID
     * @return array ['success', 'amount', 'consecutive', 'message']
     */
    public function doCheckIn($userID) {
        if (!$this->canCheckIn($userID)) {
            return [
                'success' => false,
                'amount' => 0,
                'consecutive' => $this->getConsecutiveCheckInDays($userID),
                'message' => t('Credits.AlreadyCheckedIn', '今日已签到')
            ];
        }

        // Calculate consecutive days
        $lastCheckIn = $this->getLastCheckInDate($userID);
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $consecutive = $this->getConsecutiveCheckInDays($userID);

        if ($lastCheckIn === $yesterday) {
            $consecutive++;
        } else {
            $consecutive = 1;
        }

        // Random credits
        $amount = rand(self::CREDIT_CHECKIN_MIN, self::CREDIT_CHECKIN_MAX);

        // Award credits
        $this->awardCredits($userID, $amount, 'checkin', null, null, sprintf(t('Credits.CheckInNote', '连续签到第 %d 天'), $consecutive));

        // Update check-in metadata
        Gdn::userMetaModel()->setUserMeta($userID, 'Credits.LastCheckIn', date('Y-m-d'));
        Gdn::userMetaModel()->setUserMeta($userID, 'Credits.ConsecutiveCheckIn', $consecutive);

        return [
            'success' => true,
            'amount' => $amount,
            'consecutive' => $consecutive,
            'message' => sprintf(t('Credits.CheckInSuccess', '签到成功！获得 %d 鸡腿'), $amount)
        ];
    }

    /**
     * Check if user can feed today.
     *
     * @param int $userID
     * @return bool
     */
    public function canFeed($userID) {
        $today = date('Ymd');
        $metaKey = 'Credits.DailyFeedCount.' . $today;
        $meta = Gdn::userMetaModel()->getUserMeta($userID, $metaKey, 0);
        $feedCount = (int)(reset($meta) ?: 0);
        return $feedCount < self::DAILY_FEED_QUOTA;
    }

    /**
     * Perform feed (give credits to another user).
     *
     * @param int $fromUserID
     * @param int $toUserID
     * @param int $amount
     * @param int $discussionID
     * @return array ['success', 'message']
     */
    public function doFeed($fromUserID, $toUserID, $amount, $discussionID) {
        // Validate
        if ($fromUserID === $toUserID) {
            return ['success' => false, 'message' => t('Credits.CannotFeedSelf', '不能给自己投喂')];
        }

        if ($amount < 1) {
            return ['success' => false, 'message' => t('Credits.InvalidAmount', '投喂数量无效')];
        }

        // Check daily quota
        if (!$this->canFeed($fromUserID)) {
            return ['success' => false, 'message' => t('Credits.NoFeedQuota', '今日免费投喂次数已用完')];
        }

        // Check sender's balance
        $fromUser = Gdn::userModel()->getID($fromUserID);
        $fromBalance = val('Points', $fromUser, 0);

        if ($fromBalance < $amount) {
            return ['success' => false, 'message' => t('Credits.InsufficientBalance', '鸡腿余额不足')];
        }

        $toUser = Gdn::userModel()->getID($toUserID);
        $toUserName = val('Name', $toUser, 'Unknown');

        // Deduct from sender
        $newFromBalance = $fromBalance - $amount;
        Gdn::userModel()->setField($fromUserID, 'Points', $newFromBalance);
        $this->logTransaction($fromUserID, -$amount, $newFromBalance, 'feed_give', $discussionID, $toUserID, sprintf(t('Credits.FeedGiveNote', '投喂给 %s'), $toUserName));

        // Add to receiver
        $toBalance = val('Points', $toUser, 0);
        $newToBalance = $toBalance + $amount;
        Gdn::userModel()->setField($toUserID, 'Points', $newToBalance);
        $this->logTransaction($toUserID, $amount, $newToBalance, 'feed_receive', $discussionID, $fromUserID, sprintf(t('Credits.FeedReceiveNote', '收到 %s 的投喂'), val('Name', $fromUser, 'Unknown')));

        // Update feed count
        $today = date('Ymd');
        $metaKey = 'Credits.DailyFeedCount.' . $today;
        $meta = Gdn::userMetaModel()->getUserMeta($fromUserID, $metaKey, 0);
        $feedCount = (int)(reset($meta) ?: 0);
        Gdn::userMetaModel()->setUserMeta($fromUserID, $metaKey, $feedCount + 1);

        // Update levels
        $this->updateUserLevel($fromUserID, $newFromBalance);
        $this->updateUserLevel($toUserID, $newToBalance);

        return [
            'success' => true,
            'message' => sprintf(t('Credits.FeedSuccess', '成功投喂 %d 鸡腿给 %s'), $amount, $toUserName)
        ];
    }

    /**
     * Get credit log for user.
     *
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCreditLog($userID, $limit = 20, $offset = 0) {
        return Gdn::sql()
            ->select('*')
            ->from('CreditLog')
            ->where('UserID', $userID)
            ->orderBy('DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Get total count of credit log entries for user.
     *
     * @param int $userID
     * @return int
     */
    public function getCreditLogCount($userID) {
        return Gdn::sql()
            ->select('LogID', 'count', 'Count')
            ->from('CreditLog')
            ->where('UserID', $userID)
            ->get()
            ->value('Count', 0);
    }

    /**
     * Get check-in calendar data for current month.
     *
     * @param int $userID
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function getCheckInCalendar($userID, $year = null, $month = null) {
        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $logs = Gdn::sql()
            ->select('DateInserted')
            ->from('CreditLog')
            ->where('UserID', $userID)
            ->where('Type', 'checkin')
            ->where('DateInserted >=', $startDate)
            ->where('DateInserted <=', $endDate . ' 23:59:59')
            ->get()
            ->resultArray();

        $checkedDays = [];
        foreach ($logs as $log) {
            $day = date('j', strtotime($log['DateInserted']));
            $checkedDays[$day] = true;
        }

        return $checkedDays;
    }

    /**
     * Add CSS and JS to pages.
     *
     * @param Gdn_Controller $sender
     */
    public function base_render_before($sender) {
        $sender->addCssFile('credits.css', 'plugins/Credits');
        $sender->addJsFile('credits.js', 'plugins/Credits');

        // Add definitions for JS
        $sender->addDefinition('Credits.CheckInUrl', url('/plugin/checkin'));
        $sender->addDefinition('Credits.FeedUrl', url('/plugin/feed'));
    }

    /**
     * Setup controller for frontend theme display.
     *
     * @param Gdn_Controller $sender
     */
    protected function setupFrontendController($sender) {
        // Use frontend theme instead of admin dashboard
        $sender->MasterView = 'default';
        Gdn_Theme::section('Vanilla');

        // Add CSS files for frontend
        $sender->addCssFile('style.css');
        $sender->addCssFile('credits.css', 'plugins/Credits');
    }

    /**
     * Level progress page - /credits/progress
     *
     * @param PluginController $sender
     */
    public function pluginController_progress_create($sender) {
        $sender->permission('Garden.SignIn.Allow');
        $this->setupFrontendController($sender);

        $sender->title(t('Credits.Progress', '等级进度'));
        $sender->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.Progress', '等级进度'), 'Url' => '/progress']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        $sender->setData('Credits', $credits);
        $sender->setData('Level', self::calculateLevel($credits));
        $sender->setData('Progress', self::getProgressToNextLevel($credits));
        $sender->setData('LevelThresholds', self::LEVEL_THRESHOLDS);

        // Credit rules
        $sender->setData('CreditRules', [
            'post' => [
                'name' => t('Credits.PostRule', '发帖'),
                'amount' => '+' . self::CREDIT_POST,
                'limit' => self::DAILY_LIMIT_POST,
                'note' => t('Credits.PostRuleNote', '每个帖子')
            ],
            'comment' => [
                'name' => t('Credits.CommentRule', '评论'),
                'amount' => '+' . self::CREDIT_COMMENT,
                'limit' => self::DAILY_LIMIT_COMMENT,
                'note' => t('Credits.CommentRuleNote', '每条评论')
            ],
            'checkin' => [
                'name' => t('Credits.CheckInRule', '签到'),
                'amount' => '+' . self::CREDIT_CHECKIN_MIN . '~' . self::CREDIT_CHECKIN_MAX,
                'limit' => self::CREDIT_CHECKIN_MAX,
                'note' => t('Credits.CheckInRuleNote', '随机获得')
            ],
            'feed' => [
                'name' => t('Credits.FeedRule', '被投喂'),
                'amount' => t('Credits.Unlimited', '不限'),
                'limit' => null,
                'note' => t('Credits.FeedRuleNote', '其他用户赠送')
            ]
        ]);

        $sender->render('progress', '', 'plugins/Credits');
    }

    /**
     * Credit history page - /credits/credit
     *
     * @param PluginController $sender
     */
    public function pluginController_credit_create($sender) {
        $sender->permission('Garden.SignIn.Allow');
        $this->setupFrontendController($sender);

        $sender->title(t('Credits.CreditHistory', '鸡腿账簿'));
        $sender->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.CreditHistory', '鸡腿账簿'), 'Url' => '/credit']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        // Parse page number from URL using RequestArgs
        $args = isset($sender->RequestArgs) ? $sender->RequestArgs : [];
        $page = isset($args[0]) ? $args[0] : '';
        $pageNumber = 1;
        if (preg_match('/^p-?(\d+)$/i', $page, $matches)) {
            $pageNumber = (int)$matches[1];
        }
        $pageNumber = max(1, $pageNumber);

        $limit = 20;
        $offset = ($pageNumber - 1) * $limit;

        $totalCount = $this->getCreditLogCount($userID);

        $sender->setData('Credits', $credits);
        $sender->setData('Level', self::calculateLevel($credits));
        $sender->setData('CreditLog', $this->getCreditLog($userID, $limit, $offset));
        $sender->setData('TotalCount', $totalCount);
        $sender->setData('Page', $pageNumber);
        $sender->setData('PageSize', $limit);
        $sender->setData('TotalPages', $totalCount > 0 ? ceil($totalCount / $limit) : 1);

        $sender->render('credit', '', 'plugins/Credits');
    }

    /**
     * Check-in page - /credits/board
     *
     * @param PluginController $sender
     */
    public function pluginController_board_create($sender) {
        $sender->permission('Garden.SignIn.Allow');
        $this->setupFrontendController($sender);

        $sender->title(t('Credits.CheckIn', '每日签到'));
        $sender->setData('Breadcrumbs', [
            ['Name' => t('Home'), 'Url' => '/'],
            ['Name' => t('Credits.CheckIn', '每日签到'), 'Url' => '/board']
        ]);

        $userID = Gdn::session()->UserID;
        $user = Gdn::userModel()->getID($userID);
        $credits = val('Points', $user, 0);

        $sender->setData('Credits', $credits);
        $sender->setData('Level', self::calculateLevel($credits));
        $sender->setData('CanCheckIn', $this->canCheckIn($userID));
        $sender->setData('ConsecutiveDays', $this->getConsecutiveCheckInDays($userID));
        $sender->setData('LastCheckIn', $this->getLastCheckInDate($userID));

        // Get calendar data
        $year = date('Y');
        $month = date('m');
        $sender->setData('CalendarYear', $year);
        $sender->setData('CalendarMonth', $month);
        $sender->setData('CheckedDays', $this->getCheckInCalendar($userID, $year, $month));

        $sender->render('board', '', 'plugins/Credits');
    }

    /**
     * Check-in API - POST /plugin/checkin
     *
     * @param PluginController $sender
     */
    public function pluginController_checkin_create($sender) {
        $sender->permission('Garden.SignIn.Allow');
        $sender->Form = new Gdn_Form();

        // Force JSON delivery method
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        if (!$sender->Form->authenticatedPostBack(true)) {
            $sender->setData('Success', false);
            $sender->setData('Message', t('Invalid CSRF token'));
            $sender->render();
            return;
        }

        $userID = Gdn::session()->UserID;
        $result = $this->doCheckIn($userID);

        $sender->setData('Success', $result['success']);
        $sender->setData('Amount', $result['amount']);
        $sender->setData('Consecutive', $result['consecutive']);
        $sender->setData('Message', $result['message']);

        // Get updated credits
        $user = Gdn::userModel()->getID($userID);
        $sender->setData('Credits', val('Points', $user, 0));

        $sender->render();
    }

    /**
     * Feed API - POST /plugin/feed
     *
     * @param PluginController $sender
     */
    public function pluginController_feed_create($sender) {
        $sender->permission('Garden.SignIn.Allow');
        $sender->Form = new Gdn_Form();

        // Force JSON delivery method
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        if (!$sender->Form->authenticatedPostBack(true)) {
            $sender->setData('Success', false);
            $sender->setData('Message', t('Invalid CSRF token'));
            $sender->render();
            return;
        }

        $userID = Gdn::session()->UserID;
        $toUserID = $sender->Form->getFormValue('ToUserID');
        $amount = (int)$sender->Form->getFormValue('Amount', 1);
        $discussionID = (int)$sender->Form->getFormValue('DiscussionID', 0);

        if (!$toUserID) {
            $sender->setData('Success', false);
            $sender->setData('Message', t('Credits.InvalidUser', '目标用户无效'));
            $sender->render();
            return;
        }

        $result = $this->doFeed($userID, $toUserID, $amount, $discussionID);

        $sender->setData('Success', $result['success']);
        $sender->setData('Message', $result['message']);

        // Get updated credits
        $user = Gdn::userModel()->getID($userID);
        $sender->setData('Credits', val('Points', $user, 0));
        $sender->setData('CanFeed', $this->canFeed($userID));

        $sender->render();
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
