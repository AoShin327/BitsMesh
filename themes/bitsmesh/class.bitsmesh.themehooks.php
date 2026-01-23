<?php
/**
 * BitsMesh Theme Hooks
 *
 * Handles theme-specific customizations including category icon selection.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

/**
 * Override userUrl() to generate short space URLs.
 *
 * Format: /space/{UserID}
 * This function must be defined before the class to ensure it's loaded early.
 *
 * @param object|array $user The user object.
 * @param string $px Prefix (unused in new format).
 * @param string $method Method name (unused in new format).
 * @param bool $withDomain Include domain in URL.
 * @return string The user space URL.
 */
if (!function_exists('userUrl')) {
    function userUrl($user, $px = '', $method = '', $withDomain = true) {
        $user = (object)$user;
        $userID = isset($user->UserID) ? (int)$user->UserID : 0;

        if ($userID <= 0) {
            return url('/', $withDomain);
        }

        $result = '/space/' . $userID;

        return url($result, $withDomain);
    }
}

/**
 * Override discussionUrl() to generate short URLs.
 *
 * Format: /post-{DiscussionID} or /post-{DiscussionID}/p{page}
 * This function must be defined before the class to ensure it's loaded early.
 *
 * @param object|array $discussion The discussion object.
 * @param int|string $page Page number (optional).
 * @param bool $withDomain Include domain in URL.
 * @return string The discussion URL.
 */
if (!function_exists('discussionUrl')) {
    function discussionUrl($discussion, $page = '', $withDomain = true) {
        $discussion = (object)$discussion;
        $discussionID = isset($discussion->DiscussionID) ? (int)$discussion->DiscussionID : 0;

        if ($discussionID <= 0) {
            return url('/', $withDomain);
        }

        $result = '/post-' . $discussionID;

        // Add page number if specified and greater than 1, or if user is logged in
        if ($page && ($page > 1 || Gdn::session()->UserID)) {
            $result .= '/p' . (int)$page;
        }

        return url($result, $withDomain);
    }
}

/**
 * Override commentUrl() to generate short URLs with comment anchor.
 *
 * Format: /post-{DiscussionID}#Comment_{CommentID}
 * This function must be defined before the class to ensure it's loaded early.
 *
 * @param object|array $comment The comment object.
 * @param bool $withDomain Include domain in URL.
 * @return string The comment URL.
 */
if (!function_exists('commentUrl')) {
    function commentUrl($comment, $withDomain = true) {
        $comment = (object)$comment;
        $commentID = isset($comment->CommentID) ? (int)$comment->CommentID : 0;

        if ($commentID <= 0) {
            return url('/', $withDomain);
        }

        // If we have the DiscussionID, use the short format
        if (isset($comment->DiscussionID) && $comment->DiscussionID > 0) {
            $result = '/post-' . (int)$comment->DiscussionID . '#Comment_' . $commentID;
        } else {
            // Fallback to action path (server will redirect)
            $result = '/discussion/comment/' . $commentID . '#Comment_' . $commentID;
        }

        return url($result, $withDomain);
    }
}

/**
 * Class BitsmeshThemeHooks
 */
class BitsmeshThemeHooks extends Gdn_Plugin {

    /**
     * Available IconPark icons for category selection.
     * @var array
     */
    private static $availableIcons = [
        'all-application' => 'All Applications (Default)',
        'tea' => 'Tea (Daily)',
        'formula' => 'Formula (Tech)',
        'receiver' => 'Receiver (Info)',
        'dashboard-one' => 'Dashboard (Review)',
        'dollar' => 'Dollar (Trade)',
        'car' => 'Car (Carpool)',
        'hold-interface' => 'Hold Interface (Promo)',
        'oval-love-two' => 'Love (Life)',
        'terminal' => 'Terminal (Dev)',
        'pic-one' => 'Picture (Pics)',
        'face-recognition' => 'Face (Expose)',
        'open-one' => 'Open (Internal)',
        'experiment' => 'Experiment (Sandbox)',
    ];

    /**
     * Run once on theme enable.
     * Adds IconID column to Category table if it doesn't exist.
     * Registers custom routes for clean URLs.
     *
     * @return void
     */
    public function setup() {
        $this->structure();
        $this->registerRoutes();
    }

    /**
     * Register custom routes for BitsMesh theme.
     *
     * Note: Vanilla's router has a bug where wildcard routes (:num, :alphanum)
     * don't work correctly in matchRoute(). We use gdn_dispatcher_beforeDispatch_handler
     * instead to handle /page-N → /discussions/pN rewrites.
     *
     * @return void
     */
    private function registerRoutes() {
        // Wildcard routes don't work due to Vanilla router bug.
        // Handled in gdn_dispatcher_beforeDispatch_handler instead.
    }

    /**
     * Rewrite URLs before routing.
     *
     * Handles:
     * 1. /post-{id} → /discussion/{id}/x (new short URL format)
     * 2. /post-{id}/p{page} → /discussion/{id}/x/p{page} (with pagination)
     * 3. /discussion/{id}/* → 404 (block old format)
     * 4. /page-N → /discussions/pN (pagination)
     *
     * Note: DiscussionController action paths are preserved:
     * /discussion/comment/{id}, /discussion/bookmark/{id}, etc.
     *
     * @param Gdn_Dispatcher $sender The dispatcher instance.
     * @return void
     */
    public function gdn_dispatcher_beforeDispatch_handler($sender) {
        $request = $sender->EventArguments['Request'];
        $path = $request->path();

        // === 1. New short URL format: /post-{id} or /post-{id}/p{page} ===
        if (preg_match('#^post-(\d+)(?:/p(\d+))?$#i', $path, $matches)) {
            $discussionID = (int)$matches[1];
            $page = isset($matches[2]) ? (int)$matches[2] : 0;

            if ($discussionID > 0) {
                // Rewrite to internal format: /discussion/{id}/x
                // 'x' is a placeholder slug that Vanilla ignores
                $newPath = 'discussion/' . $discussionID . '/x';
                if ($page > 0) {
                    $newPath .= '/p' . $page;
                }
                $request->path($newPath);
            }
            return;
        }

        // === 2. Block old URL format: /discussion/{id}/* ===
        // Match /discussion/{numeric_id} or /discussion/{numeric_id}/anything
        // But NOT action paths like /discussion/comment, /discussion/bookmark, etc.
        if (preg_match('#^discussion/(\d+)(?:/|$)#i', $path)) {
            // Return 404 for old format URLs
            safeHeader('HTTP/1.1 404 Not Found', true, 404);
            $request->path('dashboard/home/filenotfound');
            return;
        }

        // === 3. User space: /space/{userid} → /profile/space/{userid} ===
        // Map short URL to internal profile action
        if (preg_match('#^space/(\d+)(?:/(general|thread|post|favorite|follow))?(?:/(following|followers))?$#i', $path, $matches)) {
            $userID = (int)$matches[1];
            $tab = isset($matches[2]) ? strtolower($matches[2]) : 'general';
            $subTab = isset($matches[3]) ? strtolower($matches[3]) : '';

            if ($userID > 0) {
                // Handle follow tab with subtab
                if ($tab === 'follow') {
                    $followTab = $subTab ?: 'following';
                    $newPath = 'profile/follow/' . $userID . '/' . $followTab;
                } else {
                    // Rewrite to internal format: /profile/space/{userid}/{tab}
                    $newPath = 'profile/space/' . $userID . '/' . $tab;
                }
                $request->path($newPath);
            }
            return;
        }

        // === 4. Block old profile URL format: /profile/{username} ===
        // Only allow /profile/space/* and action paths
        // Redirect others to new /space/{userid} format
        if (preg_match('#^profile/([^/]+)/?$#i', $path, $matches)) {
            $userRef = $matches[1];
            // Allow action paths (edit, picture, preferences, etc.)
            $allowedActions = ['space', 'edit', 'picture', 'preferences', 'connections', 'tokens', 'password', 'activity', 'notifications', 'invitations', 'count', 'nomobile', 'setting', 'follow'];
            if (!in_array(strtolower($userRef), $allowedActions)) {
                // Try to get user ID and redirect to new format
                $userModel = new UserModel();
                $user = null;

                // Check if it's a username
                if (!is_numeric($userRef)) {
                    $user = $userModel->getByUsername($userRef);
                } else {
                    $user = $userModel->getID($userRef);
                }

                if ($user && isset($user->UserID)) {
                    // 301 redirect to new URL format
                    safeHeader('HTTP/1.1 301 Moved Permanently');
                    safeHeader('Location: ' . url('/space/' . $user->UserID, true));
                    exit;
                }
            }
        }

        // === 5. Existing pagination: /page-N → /discussions/pN ===
        if (preg_match('#^page-(\d+)$#i', $path, $matches)) {
            $pageNum = (int)$matches[1];
            $request->path('discussions/p' . $pageNum);
            return;
        }

        // === 6. User setting page: /setting → /profile/setting ===
        if (preg_match('#^setting(?:/.*)?$#i', $path) || $path === 'setting') {
            $request->path('profile/setting');
            return;
        }

        // === 7. Notification page: /notification → /profile/notification ===
        if (preg_match('#^notification(?:/.*)?$#i', $path) || $path === 'notification') {
            $request->path('profile/notification');
            return;
        }
    }

    /**
     * Database structure modifications.
     *
     * @return void
     */
    public function structure() {
        $database = Gdn::database();
        $construct = $database->structure();

        // Add IconID column to Category table
        $construct->table('Category');
        if (!$construct->columnExists('IconID')) {
            $construct->column('IconID', 'varchar(50)', true);
            $construct->set(false, false);
        }

        // Add Bio, Signature, Readme columns to User table
        $construct->table('User');
        $needsUpdate = false;
        if (!$construct->columnExists('Bio')) {
            $construct->column('Bio', 'varchar(255)', true);
            $needsUpdate = true;
        }
        if (!$construct->columnExists('Signature')) {
            $construct->column('Signature', 'text', true);
            $needsUpdate = true;
        }
        if (!$construct->columnExists('Readme')) {
            $construct->column('Readme', 'text', true);
            $needsUpdate = true;
        }
        if ($needsUpdate) {
            $construct->set(false, false);
        }

        // Create UserFollow table
        require_once PATH_THEMES . '/bitsmesh/models/class.userfollowmodel.php';
        UserFollowModel::structure();

        // Add CountChickenLegs column to Discussion table (for chicken leg count on discussions)
        $construct->table('Discussion');
        if (!$construct->columnExists('CountChickenLegs')) {
            $construct->column('CountChickenLegs', 'int', '0');
            $construct->set(false, false);
        }

        // Add CountChickenLegs column to Comment table (for chicken leg count on comments)
        $construct->table('Comment');
        if (!$construct->columnExists('CountChickenLegs')) {
            $construct->column('CountChickenLegs', 'int', '0');
            $construct->set(false, false);
        }

        // Create UserComment table for comment reactions (like/dislike)
        $construct->table('UserComment')
            ->primaryKey('UserCommentID')
            ->column('UserID', 'int', false, 'index.UserID')
            ->column('CommentID', 'int', false, 'index.CommentID')
            ->column('Score', 'tinyint', '0') // 1 = like, -1 = dislike, 0 = neutral
            ->column('DateInserted', 'datetime', false)
            ->set(false, false);

        // Add unique constraint for UserID + CommentID
        $sql = Gdn::sql();
        $indexName = 'UX_UserComment';
        $existingIndex = $sql->query("SHOW INDEX FROM {$sql->Database->DatabasePrefix}UserComment WHERE Key_name = '$indexName'")->resultArray();
        if (empty($existingIndex)) {
            $sql->query("ALTER TABLE {$sql->Database->DatabasePrefix}UserComment ADD UNIQUE INDEX $indexName (UserID, CommentID)");
        }

        // Create ChickenLeg table for tracking daily chicken leg gifts
        $construct->table('ChickenLeg')
            ->primaryKey('ChickenLegID')
            ->column('UserID', 'int', false, 'index.UserID') // Who gave the chicken leg
            ->column('RecordType', 'varchar(20)', false, 'index.RecordType') // 'Discussion' or 'Comment'
            ->column('RecordID', 'int', false, 'index.RecordID') // DiscussionID or CommentID
            ->column('ReceiverUserID', 'int', false, 'index.ReceiverUserID') // Author who receives
            ->column('DateInserted', 'datetime', false, 'index.Date')
            ->set(false, false);

        // Create UserReaction table for unified reactions (like/dislike on Discussion and Comment)
        $construct->table('UserReaction')
            ->primaryKey('UserReactionID')
            ->column('UserID', 'int', false, 'index.UserID')
            ->column('RecordType', 'varchar(20)', false, 'index.RecordType') // 'Discussion' or 'Comment'
            ->column('RecordID', 'int', false, 'index.RecordID')
            ->column('Score', 'tinyint', '0') // 1 = like, -1 = dislike
            ->column('DateInserted', 'datetime', false)
            ->set(false, false);

        // Add unique constraint for UserID + RecordType + RecordID
        $indexName = 'UX_UserReaction';
        $existingIndex = $sql->query("SHOW INDEX FROM {$sql->Database->DatabasePrefix}UserReaction WHERE Key_name = '$indexName'")->resultArray();
        if (empty($existingIndex)) {
            $sql->query("ALTER TABLE {$sql->Database->DatabasePrefix}UserReaction ADD UNIQUE INDEX $indexName (UserID, RecordType, RecordID)");
        }
    }

    /**
     * Get theme colors (hardcoded green theme).
     *
     * @return array Theme colors.
     */
    private static function getThemeColors() {
        return [
            // Green theme - hardcoded
            'primary' => '#22C55E',
            'secondary' => '#4ADE80',
            'darkPrimary' => '#16A34A',
            'darkSecondary' => '#22C55E',
            // Grid colors - hardcoded
            'grid' => '#d4d4d4',
            'darkGrid' => '#404040',
            'gridBg' => '#fffcf8',
            'darkGridBg' => '#1a1a1a',
        ];
    }

    /**
     * Inject dynamic theme styles into page head.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectThemeStyles($sender) {
        $colors = self::getThemeColors();

        // Sanitize color values for CSS injection
        foreach ($colors as $key => $value) {
            $colors[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        $css = '<style id="bitsmesh-dynamic-vars">
:root {
    --bits-primary: ' . $colors['primary'] . ';
    --bits-secondary: ' . $colors['secondary'] . ';
}
body.dark-layout {
    --bits-primary: ' . $colors['darkPrimary'] . ';
    --bits-secondary: ' . $colors['darkSecondary'] . ';
}
@media only screen and (min-width: 500px) {
    body {
        background-color: ' . $colors['gridBg'] . ';
        background-image: linear-gradient(' . $colors['grid'] . ' 1px, transparent 0),
                          linear-gradient(90deg, ' . $colors['grid'] . ' 1px, transparent 0);
        background-size: 32px 32px;
    }
    body.dark-layout {
        background-color: ' . $colors['darkGridBg'] . ';
        background-image: linear-gradient(' . $colors['darkGrid'] . ' 1px, transparent 1px),
                          linear-gradient(to right, ' . $colors['darkGrid'] . ' 1px, transparent 1px);
        background-size: 32px 32px;
    }
}
</style>';

        if ($sender->Head) {
            $sender->Head->addString($css);
        }
    }

    /**
     * Inject category list data for Smarty templates.
     *
     * Fetches all visible categories with their icons for sidebar display.
     * Follows modern forum style with icons for each category.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectCategoryListData($sender) {
        $categories = [];

        try {
            // Get all categories
            $allCategories = CategoryModel::categories();

            if ($allCategories && is_array($allCategories)) {
                $session = Gdn::session();

                foreach ($allCategories as $category) {
                    // Skip root category and archived categories
                    $categoryID = val('CategoryID', $category, 0);
                    $parentID = val('ParentCategoryID', $category, -1);
                    $archived = val('Archived', $category, 0);

                    // Only show top-level categories (ParentCategoryID = -1) that are not archived
                    if ($parentID != -1 || $archived) {
                        continue;
                    }

                    // Check view permission
                    if (!CategoryModel::checkPermission($category, 'Vanilla.Discussions.View')) {
                        continue;
                    }

                    // Get category icon (use default if not set)
                    $iconId = val('IconID', $category, '');
                    if (empty($iconId) || !array_key_exists($iconId, self::$availableIcons)) {
                        $iconId = 'all-application'; // Default icon
                    }

                    $categories[] = [
                        'CategoryID' => $categoryID,
                        'Name' => val('Name', $category, ''),
                        'Url' => categoryUrl($category),
                        'IconID' => $iconId,
                        'CountDiscussions' => val('CountDiscussions', $category, 0),
                    ];
                }
            }
        } catch (Exception $e) {
            // Silently fail - category list will be empty
        }

        $sender->setData('SidebarCategories', $categories);
        $sender->setData('SidebarCategoriesUrl', url('/categories'));
    }

    /**
     * Inject sidebar data for Smarty templates.
     *
     * Sets data that will be available in sidebar-welcome.tpl:
     * - SidebarIsLoggedIn: boolean
     * - SidebarSiteTitle: string
     * - SidebarSiteDescription: string
     * - SidebarUserCount: int
     * - SidebarUserDiscussionCount: int
     * - SidebarUserCommentCount: int
     * - SidebarUserName: string
     * - SidebarUserPhoto: string
     * - SidebarUserProfileUrl: string
     * - SidebarSignInUrl: string
     * - SidebarRegisterUrl: string
     * - SidebarNewMembers: array
     * - SidebarCategoryName: string (category pages only)
     * - SidebarCategoryDescription: string (category pages only)
     * - isCategoryPage: boolean
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectSidebarData($sender) {
        $session = Gdn::session();
        $isLoggedIn = $session->isValid();

        // Site info
        $siteTitle = c('Garden.HomepageTitle', c('Garden.Title', 'Welcome'));
        $siteDescription = c('Garden.Description', '');

        // User statistics
        $userCount = 0;
        try {
            $userModel = new UserModel();
            $userCount = $userModel->getCountLike();
        } catch (Exception $e) {
            // Silently fail - stats panel will show 0
        }

        // User's discussion count and comment count (for logged in users)
        $userDiscussionCount = 0;
        $userCommentCount = 0;
        $userName = '';
        $userPhoto = '';
        $userProfileUrl = '';
        $userSpaceUrl = '';
        $userBookmarkCount = 0;
        $userFollowingCount = 0;
        $userFollowersCount = 0;
        $unreadNotifications = 0;

        if ($isLoggedIn && $session->User) {
            $userDiscussionCount = val('CountDiscussions', $session->User, 0);
            $userCommentCount = val('CountComments', $session->User, 0);
            $userBookmarkCount = val('CountBookmarks', $session->User, 0);
            $userName = val('Name', $session->User, '');
            $userProfileUrl = userUrl($session->User);
            $userSpaceUrl = url('/space/' . val('UserID', $session->User));

            // Get user photo URL
            $photo = val('Photo', $session->User, '');
            if ($photo) {
                if (!isUrl($photo)) {
                    $photo = Gdn_Upload::url(changeBasename($photo, 'n%s'));
                }
            } else {
                // Use default avatar
                $photo = UserModel::getDefaultAvatarUrl($session->User);
            }
            $userPhoto = $photo;

            // Get follow/followers count
            try {
                require_once PATH_THEMES . '/bitsmesh/models/class.userfollowmodel.php';
                $followModel = UserFollowModel::instance();
                $userFollowingCount = $followModel->getFollowingCount($session->UserID);
                $userFollowersCount = $followModel->getFollowersCount($session->UserID);
            } catch (Exception $e) {
                // Silently fail
            }

            // Get unread notification count
            // Count actual pending Activity records (same as notification page)
            $unreadNotifications = 0;
            try {
                $activityModel = new ActivityModel();

                // Get activity types for notifications
                $commentType = ActivityModel::getActivityType('Comment');
                $discussionType = ActivityModel::getActivityType('Discussion');
                $discCommentType = ActivityModel::getActivityType('DiscussionComment');
                $discMentionType = ActivityModel::getActivityType('DiscussionMention');
                $commentMentionType = ActivityModel::getActivityType('CommentMention');

                $notificationTypeIDs = [];
                if ($commentType) {
                    $notificationTypeIDs[] = val('ActivityTypeID', $commentType);
                }
                if ($discussionType) {
                    $notificationTypeIDs[] = val('ActivityTypeID', $discussionType);
                }
                if ($discCommentType) {
                    $notificationTypeIDs[] = val('ActivityTypeID', $discCommentType);
                }
                if ($discMentionType) {
                    $notificationTypeIDs[] = val('ActivityTypeID', $discMentionType);
                }
                if ($commentMentionType) {
                    $notificationTypeIDs[] = val('ActivityTypeID', $commentMentionType);
                }

                // Count pending notifications (Notified = SENT_PENDING = 3)
                if (!empty($notificationTypeIDs)) {
                    $unreadNotifications = Gdn::sql()
                        ->select('ActivityID', 'count', 'Count')
                        ->from('Activity')
                        ->where('NotifyUserID', $session->UserID)
                        ->whereIn('ActivityTypeID', $notificationTypeIDs)
                        ->where('Notified', ActivityModel::SENT_PENDING)
                        ->get()
                        ->firstRow(DATASET_TYPE_ARRAY);
                    $unreadNotifications = $unreadNotifications ? (int)val('Count', $unreadNotifications, 0) : 0;
                }

                // Add unread conversations count
                $unreadConversations = (int)val('CountUnreadConversations', $session->User, 0);
                $unreadNotifications += $unreadConversations;
            } catch (Exception $e) {
                // Silently fail
            }
        }

        // Get newest members (limit to 8)
        $newMembers = [];
        try {
            $userModel = new UserModel();
            $newMembersData = $userModel->getWhere(
                ['Deleted' => 0, 'Banned' => 0],
                'DateInserted',
                'desc',
                8
            );
            if ($newMembersData) {
                foreach ($newMembersData as $member) {
                    $memberPhoto = val('Photo', $member, '');
                    if ($memberPhoto && !isUrl($memberPhoto)) {
                        $memberPhoto = Gdn_Upload::url(changeBasename($memberPhoto, 'n%s'));
                    } elseif (!$memberPhoto) {
                        $memberPhoto = UserModel::getDefaultAvatarUrl($member);
                    }

                    $newMembers[] = [
                        'Name' => val('Name', $member, ''),
                        'Photo' => $memberPhoto,
                        'Url' => userUrl($member),
                    ];
                }
            }
        } catch (Exception $e) {
            // Silently fail - new members list will be empty
        }

        // Set all sidebar data
        $sender->setData('SidebarIsLoggedIn', $isLoggedIn);
        $sender->setData('SidebarSiteTitle', $siteTitle);
        $sender->setData('SidebarSiteDescription', $siteDescription);
        $sender->setData('SidebarUserCount', $userCount);
        $sender->setData('SidebarUserDiscussionCount', $userDiscussionCount);
        $sender->setData('SidebarUserCommentCount', $userCommentCount);
        $sender->setData('SidebarUserBookmarkCount', $userBookmarkCount);
        $sender->setData('SidebarUserFollowingCount', $userFollowingCount);
        $sender->setData('SidebarUserFollowersCount', $userFollowersCount);
        $sender->setData('SidebarUnreadNotifications', $unreadNotifications);
        $sender->setData('SidebarUserName', $userName);
        $sender->setData('SidebarUserPhoto', $userPhoto);
        $sender->setData('SidebarUserProfileUrl', $userProfileUrl);
        $sender->setData('SidebarUserSpaceUrl', $userSpaceUrl);
        $sender->setData('SidebarNewMembers', $newMembers);
        $sender->setData('SidebarSignInUrl', url('/entry/signin'));
        $sender->setData('SidebarRegisterUrl', url('/entry/register'));
        $sender->setData('SidebarSignOutUrl', url(signOutUrl()));
        $sender->setData('SidebarNewDiscussionUrl', url('/post/discussion'));
        $sender->setData('SidebarCategoriesUrl', url('/categories'));
        $sender->setData('SidebarDiscussionsUrl', url('/discussions'));
        $sender->setData('SidebarActivityUrl', url('/activity'));
        $sender->setData('SidebarMyDiscussionsUrl', url('/discussions/mine'));
        // Bookmarks URL points to user space favorite tab
        $sender->setData('SidebarBookmarksUrl', $isLoggedIn && $session->User
            ? url('/space/' . val('UserID', $session->User) . '/favorite')
            : url('/discussions/bookmarked'));
        $sender->setData('SidebarSettingsUrl', url('/setting'));
        // Follow list URL
        $sender->setData('SidebarFollowingUrl', $isLoggedIn && $session->User
            ? url('/space/' . $session->UserID . '/follow/following')
            : '');
        $sender->setData('SidebarFollowersUrl', $isLoggedIn && $session->User
            ? url('/space/' . $session->UserID . '/follow/followers')
            : '');

        // Category page detection and data injection
        // Check if we're on a category page (CategoriesController with a specific category)
        $isCategoryPage = false;
        $categoryName = '';
        $categoryDescription = '';

        $controllerName = strtolower(get_class($sender));
        if (strpos($controllerName, 'categoriescontroller') !== false) {
            // Get category data from the controller
            $category = $sender->data('Category');
            if ($category && is_object($category)) {
                $isCategoryPage = true;
                $categoryName = val('Name', $category, '');
                $categoryDescription = val('Description', $category, '');
            } elseif ($category && is_array($category)) {
                $isCategoryPage = true;
                $categoryName = isset($category['Name']) ? $category['Name'] : '';
                $categoryDescription = isset($category['Description']) ? $category['Description'] : '';
            }
        }

        $sender->setData('isCategoryPage', $isCategoryPage);
        $sender->setData('SidebarCategoryName', $categoryName);
        $sender->setData('SidebarCategoryDescription', $categoryDescription);
    }

    /**
     * Add icon picker to category add/edit form.
     *
     * @param SettingsController $sender The controller instance.
     * @param array $args Event arguments.
     * @return void
     */
    public function settingsController_addEditCategory_handler($sender, $args) {
        // Add JS and CSS for icon picker
        $sender->addJsFile('icon-picker.js', 'themes/bitsmesh');
        $sender->addCssFile('icon-picker.css', 'themes/bitsmesh');

        // Get current icon value
        $category = val('Category', $sender, null);
        $currentIcon = '';
        if ($category) {
            $currentIcon = val('IconID', $category, '');
        }

        // Set available icons for the view
        $sender->setData('AvailableIcons', self::$availableIcons);
        $sender->setData('CurrentIcon', $currentIcon);

        // Add the IconID field to extended fields
        if (!isset($sender->Data['_ExtendedFields'])) {
            $sender->Data['_ExtendedFields'] = [];
        }

        // Create custom icon picker HTML
        $iconPickerHtml = $this->getIconPickerHtml($currentIcon);
        $sender->setData('IconPickerHtml', $iconPickerHtml);
    }

    /**
     * Generate icon picker HTML.
     *
     * @param string $currentIcon Current selected icon ID.
     * @return string HTML for icon picker.
     */
    private function getIconPickerHtml($currentIcon) {
        $html = '<li class="form-group" id="IconPickerGroup">';
        $html .= '<div class="label-wrap"><label for="Form_IconID">' . t('Category Icon') . '</label></div>';
        $html .= '<div class="input-wrap">';
        $html .= '<div class="bits-icon-picker">';
        $html .= '<input type="hidden" name="IconID" id="Form_IconID" value="' . htmlspecialchars($currentIcon) . '">';

        // Current selection preview
        $html .= '<div class="bits-icon-preview">';
        $html .= '<svg class="iconpark-icon" id="IconPreview" width="24" height="24"><use href="#' . ($currentIcon ?: 'all-application') . '"></use></svg>';
        $html .= '<span class="bits-icon-name" id="IconName">' . htmlspecialchars(self::$availableIcons[$currentIcon] ?? 'Select an icon') . '</span>';
        $html .= '<button type="button" class="btn btn-secondary bits-icon-toggle" id="IconPickerToggle">' . t('Change') . '</button>';
        $html .= '</div>';

        // Icon grid (hidden by default)
        $html .= '<div class="bits-icon-grid" id="IconGrid" style="display:none">';
        foreach (self::$availableIcons as $iconId => $iconName) {
            $selectedClass = ($iconId === $currentIcon) ? ' selected' : '';
            $html .= '<button type="button" class="bits-icon-option' . $selectedClass . '" data-icon="' . htmlspecialchars($iconId) . '" title="' . htmlspecialchars($iconName) . '">';
            $html .= '<svg class="iconpark-icon" width="20" height="20"><use href="#' . htmlspecialchars($iconId) . '"></use></svg>';
            $html .= '</button>';
        }
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    /**
     * Inject icon picker into edit category form after category settings.
     * The Twig template fires this event without fireAs(), so use VanillaSettingsController prefix.
     *
     * @param object $sender The pluggable object.
     * @param array $args Event arguments.
     * @return void
     */
    public function vanillaSettingsController_afterCategorySettings_handler($sender, $args) {
        // Get the controller
        $controller = Gdn::controller();
        if (!$controller) {
            return;
        }

        // Output the icon picker HTML
        $iconPickerHtml = val('IconPickerHtml', $controller->Data, '');
        if ($iconPickerHtml) {
            echo $iconPickerHtml;
        }
    }

    /**
     * Save IconID when category is saved.
     *
     * @param CategoryModel $sender The CategoryModel instance.
     * @param array $args Event arguments.
     * @return void
     */
    public function categoryModel_beforeSaveCategory_handler($sender, $args) {
        $formPostValues = &$args['FormPostValues'];

        // Validate and sanitize IconID
        if (isset($formPostValues['IconID'])) {
            $iconId = trim($formPostValues['IconID']);

            // Validate icon ID against whitelist
            if (!empty($iconId) && !array_key_exists($iconId, self::$availableIcons)) {
                $iconId = ''; // Reset to empty if invalid
            }

            $formPostValues['IconID'] = $iconId;
        }
    }

    /**
     * Categories controller render before hook.
     * Force show category link on category pages for style consistency with homepage.
     *
     * @param CategoriesController $sender The controller instance.
     * @return void
     */
    public function categoriesController_render_before($sender) {
        // Force display category tag on category pages (consistent with homepage style)
        $sender->setData('_ShowCategoryLink', true);
    }

    /**
     * Render before hook - inject dynamic styles and resources.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    public function base_render_before($sender) {
        // Frontend: inject dynamic theme styles and load JS files
        // Check for Vanilla section OR NOT in Dashboard section
        // PluginController pages may have both 'Dashboard' and 'Vanilla' sections
        // so we need to check for 'Vanilla' presence as priority
        $isFrontend = inSection('Vanilla') || !inSection('Dashboard');

        if ($isFrontend) {
            $this->injectThemeStyles($sender);
            $this->injectSidebarData($sender);
            $this->injectCategoryListData($sender);
            $this->injectPostListControlerData($sender);

            // Remove Vanilla's default CategoriesModule from Panel assets
            // (we use custom bits-category-panel instead)
            if (isset($sender->Assets['Panel']['CategoriesModule'])) {
                unset($sender->Assets['Panel']['CategoriesModule']);
            }

            // Load theme JavaScript files
            $sender->addJsFile('darkMode.js', 'themes/bitsmesh');
            $sender->addJsFile('theme.js', 'themes/bitsmesh');

            return;
        }

        // Dashboard: handle specific pages
        $controllerName = strtolower($sender->ControllerName);
        $method = strtolower(val('RequestMethod', $sender, ''));

        // Category add/edit pages: include SVG sprite
        if ($controllerName === 'vanillasettingscontroller' &&
            ($method === 'addcategory' || $method === 'editcategory')) {

            // Include SVG sprite for icon preview
            $spritePath = PATH_THEMES . '/bitsmesh/views/iconpark-sprite.tpl';
            if (file_exists($spritePath)) {
                $sender->addAsset('Content', $this->getSvgSpriteHtml(), 'IconParkSprite');
            }
        }
    }

    /**
     * Get SVG sprite HTML for dashboard.
     *
     * @return string SVG sprite HTML.
     */
    private function getSvgSpriteHtml() {
        $spritePath = PATH_THEMES . '/bitsmesh/views/iconpark-sprite.tpl';
        if (!file_exists($spritePath)) {
            return '';
        }

        // Read the sprite file and extract the SVG content
        $content = file_get_contents($spritePath);

        // Remove Smarty comments
        $content = preg_replace('/\{\*.*?\*\}/s', '', $content);

        return $content;
    }

    /**
     * Get available icons list.
     *
     * @return array Available icons.
     */
    public static function getAvailableIcons() {
        return self::$availableIcons;
    }

    /**
     * Inject post list controller data for Smarty templates.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectPostListControlerData($sender) {
        // Default values - always set to prevent template errors
        $sender->setData('BitsShowPostListControler', false);
        $sender->setData('BitsCurrentSort', 'posts');
        $sender->setData('BitsSortCommentsUrl', '');
        $sender->setData('BitsSortPostsUrl', '');
        $sender->setData('BitsCurrentPage', 1);
        $sender->setData('BitsTotalPages', 1);
        $sender->setData('BitsShowPager', false);
        $sender->setData('BitsPagerPages', []);

        // Only inject on discussion list pages
        $controllerName = strtolower(get_class($sender));
        $isDiscussionsList = (
            strpos($controllerName, 'discussionscontroller') !== false ||
            strpos($controllerName, 'categoriescontroller') !== false
        );

        if (!$isDiscussionsList) {
            return;
        }

        // Get current path for building URLs
        $request = Gdn::request();
        $currentPath = $request->path();

        // Extract current page number from path (e.g., discussions/p2 -> 2)
        $currentPage = 1;
        if (preg_match('#/p(\d+)/?$#i', $currentPath, $pageMatches)) {
            $currentPage = (int)$pageMatches[1];
        }

        // Clean page numbers from path
        $basePath = preg_replace('#/p\d+/?$#i', '', $currentPath);

        // BitsMesh: Simplify homepage URL
        // Use root path (/) instead of /discussions for cleaner URLs
        // This makes URLs like /?sortBy=replyTime instead of /discussions?sortBy=replyTime
        $isHomepage = empty($basePath) || $basePath === 'discussions';
        if ($isHomepage) {
            $basePath = '';
        }

        // Generate sort URLs using custom sortBy parameter
        // sortBy=replyTime (新评论 - sort by latest reply time)
        // sortBy=postTime (新帖子 - sort by post creation time)
        // DiscussionModel::getSortFromArray() maps these to internal sort keys
        // For homepage, default view (replyTime) uses clean URL without sortBy
        // IMPORTANT: Preserve current page number when switching sort mode
        if ($isHomepage) {
            if ($currentPage > 1) {
                // On page 2+, include page number in sort URLs
                $sortCommentsUrl = url('/page-' . $currentPage);
                $sortPostsUrl = url('/page-' . $currentPage . '?sortBy=postTime');
            } else {
                // On page 1, use clean URLs
                $sortCommentsUrl = url('/');
                $sortPostsUrl = url('/?sortBy=postTime');
            }
        } else {
            // Category pages - use standard format with page preservation
            if ($currentPage > 1) {
                $sortCommentsUrl = url($basePath . '/p' . $currentPage . '?sortBy=replyTime');
                $sortPostsUrl = url($basePath . '/p' . $currentPage . '?sortBy=postTime');
            } else {
                $sortCommentsUrl = url($basePath . '?sortBy=replyTime');
                $sortPostsUrl = url($basePath . '?sortBy=postTime');
            }
        }

        // Determine current sort from sortBy parameter
        // Default to 'comments' (replyTime/hot - by latest reply)
        $currentSort = 'comments';
        $sortByParam = $request->get('sortBy', '');
        if ($sortByParam === 'postTime') {
            $currentSort = 'posts';
        }

        // Set template data
        $sender->setData('BitsShowPostListControler', true);
        $sender->setData('BitsCurrentSort', $currentSort);
        $sender->setData('BitsSortCommentsUrl', $sortCommentsUrl);
        $sender->setData('BitsSortPostsUrl', $sortPostsUrl);

        // Get pager data from PagerModule
        // Pass sortBy value to ensure it's preserved in pager URLs
        // For homepage with default sort (replyTime), no sortBy param needed
        $sortByValue = ($currentSort === 'posts') ? 'postTime' : '';
        $this->injectPagerData($sender, $basePath, $sortByValue);
    }

    /**
     * Inject pager data for Smarty templates.
     *
     * Calculates page numbers to display and generates URLs.
     * Uses modern forum style pagination: 1 2 3 4 5 .. 100
     *
     * @param Gdn_Controller $sender The controller instance.
     * @param string $basePath The base URL path for building pager links.
     * @param string $sortBy The sortBy parameter value (postTime or replyTime).
     * @return void
     */
    private function injectPagerData($sender, $basePath, $sortBy = 'replyTime') {
        // Try to get pager from controller data first
        $pager = PagerModule::current();

        // Fallback to controller's pager data
        $offset = 0;
        $limit = c('Vanilla.Discussions.PerPage', 30);
        $totalRecords = 0;

        if ($pager && is_object($pager)) {
            $offset = isset($pager->Offset) ? (int)$pager->Offset : 0;
            $limit = isset($pager->Limit) && $pager->Limit > 0 ? (int)$pager->Limit : $limit;
            $totalRecords = isset($pager->TotalRecords) ? (int)$pager->TotalRecords : 0;
        }

        // Also check controller data for fallback
        if ($totalRecords <= 0) {
            $totalRecords = (int)$sender->data('RecordCount', 0);
        }
        if ($totalRecords <= 0) {
            $totalRecords = (int)$sender->data('CountDiscussions', 0);
        }

        // Last resort: query DiscussionModel for count
        // For category pages, get count for specific category only
        if ($totalRecords <= 0) {
            try {
                $discussionModel = new DiscussionModel();

                // Check if we're on a category page and get category-specific count
                $categoryID = $sender->data('Category.CategoryID', 0);
                if ($categoryID > 0) {
                    // Get count for this category only
                    $totalRecords = $discussionModel->getCount(['d.CategoryID' => $categoryID]);
                } else {
                    // Homepage: get total count
                    $totalRecords = $discussionModel->getCount();
                }
            } catch (Exception $e) {
                // Silently fail
            }
        }

        // Get current page from URL
        $request = Gdn::request();
        $path = $request->path();
        if (preg_match('#/p(\d+)/?$#i', $path, $matches)) {
            $currentPage = (int)$matches[1];
        } else {
            $currentPage = 1;
        }

        // Calculate total pages
        $totalPages = $limit > 0 && $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;

        // Ensure valid values
        $currentPage = max(1, min($currentPage, $totalPages));
        $totalPages = max(1, $totalPages);

        // Only show pager if there are multiple pages
        if ($totalPages <= 1) {
            $sender->setData('BitsShowPager', false);
            return;
        }

        // Build page URLs with sortBy parameter (only if not default sort)
        // Default sort (replyTime) uses clean URLs without sortBy param
        $queryString = !empty($sortBy) ? '?sortBy=' . urlencode($sortBy) : '';

        // Build array of pages to display (modern forum style)
        $pages = $this->buildPagerPages($currentPage, $totalPages, $basePath, $queryString);

        // Build prev/next URLs
        $prevUrl = $currentPage > 1 ? $this->buildPageUrl($basePath, $currentPage - 1, $queryString) : '';
        $nextUrl = $currentPage < $totalPages ? $this->buildPageUrl($basePath, $currentPage + 1, $queryString) : '';

        // Set pager data
        $sender->setData('BitsShowPager', true);
        $sender->setData('BitsCurrentPage', $currentPage);
        $sender->setData('BitsTotalPages', $totalPages);
        $sender->setData('BitsPagerPages', $pages);
        $sender->setData('BitsPagerPrevUrl', $prevUrl);
        $sender->setData('BitsPagerNextUrl', $nextUrl);
    }

    /**
     * Build array of page info for the pager template.
     *
     * Returns an array like:
     * [
     *   ['page' => 1, 'url' => '...', 'current' => true],
     *   ['page' => 2, 'url' => '...', 'current' => false],
     *   ['page' => '..', 'url' => '', 'ellipsis' => true],
     *   ['page' => 100, 'url' => '...', 'current' => false],
     * ]
     *
     * @param int $currentPage Current page number.
     * @param int $totalPages Total number of pages.
     * @param string $basePath Base URL path.
     * @param string $queryString Query string parameters.
     * @return array Array of page info.
     */
    private function buildPagerPages($currentPage, $totalPages, $basePath, $queryString) {
        $pages = [];
        $range = 2; // Show 2 pages on each side of current

        // Simple case: show all pages if 7 or fewer
        if ($totalPages <= 7) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'page' => $i,
                    'url' => $this->buildPageUrl($basePath, $i, $queryString),
                    'current' => ($i === $currentPage),
                    'ellipsis' => false,
                ];
            }
            return $pages;
        }

        // Complex case: show pages with ellipsis
        $showStart = max(1, $currentPage - $range);
        $showEnd = min($totalPages, $currentPage + $range);

        // Adjust range to always show 5 consecutive pages when possible
        if ($showEnd - $showStart < 4) {
            if ($showStart === 1) {
                $showEnd = min($totalPages, 5);
            } elseif ($showEnd === $totalPages) {
                $showStart = max(1, $totalPages - 4);
            }
        }

        // Add first page if not in range
        if ($showStart > 1) {
            $pages[] = [
                'page' => 1,
                'url' => $this->buildPageUrl($basePath, 1, $queryString),
                'current' => (1 === $currentPage),
                'ellipsis' => false,
            ];

            // Add ellipsis if there's a gap
            if ($showStart > 2) {
                $pages[] = [
                    'page' => '..',
                    'url' => '',
                    'current' => false,
                    'ellipsis' => true,
                ];
            }
        }

        // Add pages in range
        for ($i = $showStart; $i <= $showEnd; $i++) {
            $pages[] = [
                'page' => $i,
                'url' => $this->buildPageUrl($basePath, $i, $queryString),
                'current' => ($i === $currentPage),
                'ellipsis' => false,
            ];
        }

        // Add last page if not in range
        if ($showEnd < $totalPages) {
            // Add ellipsis if there's a gap
            if ($showEnd < $totalPages - 1) {
                $pages[] = [
                    'page' => '..',
                    'url' => '',
                    'current' => false,
                    'ellipsis' => true,
                ];
            }

            $pages[] = [
                'page' => $totalPages,
                'url' => $this->buildPageUrl($basePath, $totalPages, $queryString),
                'current' => ($totalPages === $currentPage),
                'ellipsis' => false,
            ];
        }

        return $pages;
    }

    /**
     * Build URL for a specific page number.
     *
     * @param string $basePath Base URL path.
     * @param int $page Page number.
     * @param string $queryString Query string parameters.
     * @return string Full URL for the page.
     */
    private function buildPageUrl($basePath, $page, $queryString) {
        // Handle empty basePath (homepage)
        $isHomepage = ($basePath === '' || $basePath === '/');

        // Page 1 doesn't need page number in URL
        if ($page <= 1) {
            if ($isHomepage) {
                // Homepage page 1: / or /?sortBy=xxx
                return url('/' . $queryString);
            }
            return url($basePath . $queryString);
        }

        // Append page number
        if ($isHomepage) {
            // Homepage pagination: /page-2?sortBy=xxx
            // Uses custom route mapping: /page-N → /discussions/pN
            return url('/page-' . $page . $queryString);
        }

        // Other pages (categories): /categories/xxx/p2?sortBy=xxx
        return url($basePath . '/p' . $page . $queryString);
    }

    /**
     * Add BitsMesh settings menu item to Dashboard.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    public function base_getAppSettingsMenuItems_handler($sender) {
        $menu = &$sender->EventArguments['SideMenu'];
        if ($menu) {
            $menu->addLink('Site', t('BitsMesh'), 'dashboard/settings/bitsmesh', 'Garden.Settings.Manage', ['class' => 'nav-bitsmesh']);
        }
    }

    /**
     * BitsMesh settings page - /dashboard/settings/bitsmesh
     *
     * @param SettingsController $sender The controller instance.
     * @return void
     */
    public function settingsController_bitsmesh_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->setHighlightRoute('dashboard/settings/bitsmesh');
        $sender->title(t('BitsMesh 主题设置'));

        // Configuration keys
        $configKeys = [
            'CheckIn_DistributionN' => 'BitsMesh.CheckIn.DistributionN',
            'CheckIn_DistributionP' => 'BitsMesh.CheckIn.DistributionP',
            'CheckIn_MinAmount' => 'BitsMesh.CheckIn.MinAmount',
        ];

        // Default values
        $defaults = [
            'CheckIn_DistributionN' => 50,
            'CheckIn_DistributionP' => 0.1,
            'CheckIn_MinAmount' => 1,
        ];

        // Load current values
        foreach ($configKeys as $formKey => $configKey) {
            $sender->setData($formKey, c($configKey, $defaults[$formKey]));
        }

        // Handle form submission
        if ($sender->Form->authenticatedPostBack()) {
            // Get and validate form values
            $n = (int)$sender->Form->getFormValue('CheckIn_DistributionN', 50);
            $p = (float)$sender->Form->getFormValue('CheckIn_DistributionP', 0.1);
            $min = (int)$sender->Form->getFormValue('CheckIn_MinAmount', 1);

            // Validate ranges
            $errors = [];
            if ($n < 10 || $n > 200) {
                $errors[] = t('最大鸡腿数必须在 10-200 之间');
            }
            if ($p < 0.01 || $p > 0.5) {
                $errors[] = t('成功概率必须在 0.01-0.5 之间');
            }
            if ($min < 1 || $min > 10) {
                $errors[] = t('保底最小值必须在 1-10 之间');
            }
            if ($min > $n) {
                $errors[] = t('保底最小值不能大于最大鸡腿数');
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $sender->Form->addError($error);
                }
            } else {
                // Save configuration
                saveToConfig('BitsMesh.CheckIn.DistributionN', $n);
                saveToConfig('BitsMesh.CheckIn.DistributionP', $p);
                saveToConfig('BitsMesh.CheckIn.MinAmount', $min);

                // Update view data
                $sender->setData('CheckIn_DistributionN', $n);
                $sender->setData('CheckIn_DistributionP', $p);
                $sender->setData('CheckIn_MinAmount', $min);

                $sender->informMessage(t('设置已保存'));
            }
        }

        // Set form values
        $sender->Form->setData([
            'CheckIn_DistributionN' => $sender->data('CheckIn_DistributionN'),
            'CheckIn_DistributionP' => $sender->data('CheckIn_DistributionP'),
            'CheckIn_MinAmount' => $sender->data('CheckIn_MinAmount'),
        ]);

        $sender->render('bitsmesh', '', 'themes/bitsmesh');
    }

    /**
     * User space page - /space/{userid}
     *
     * Modern forum style user profile page with statistics cards,
     * discussion/comment lists, and tab navigation.
     *
     * @param ProfileController $sender The controller instance.
     * @param int $userID User ID to display.
     * @param string $tab Current tab (general, thread, post, favorite).
     * @return void
     */
    public function profileController_space_create($sender, $userID = 0, $tab = 'general') {
        $userID = (int)$userID;

        // Validate user ID
        if ($userID <= 0) {
            throw notFoundException('User');
        }

        // Get user data
        $userModel = new UserModel();
        $user = $userModel->getID($userID, DATASET_TYPE_ARRAY);

        if (!$user) {
            throw notFoundException('User');
        }

        // Convert to object for easier access
        $user = (object)$user;

        // Set up the page
        $sender->setData('User', $user);
        $sender->setData('UserID', $userID);
        $sender->setData('Tab', strtolower($tab));

        // Page title
        $sender->title(sprintf(t('%s\'s Space'), htmlspecialchars($user->Name)));

        // Add CSS
        $sender->addCssFile('bits-space.css', 'themes/bitsmesh');

        // Calculate user statistics
        $joinDays = floor((time() - strtotime($user->DateInserted)) / 86400);
        $sender->setData('JoinDays', $joinDays);

        // Get user level from Credits plugin if available
        $level = 1;
        $credits = 0;
        if (class_exists('CreditsPlugin')) {
            $creditsMeta = Gdn::userMetaModel()->getUserMeta($userID, 'Credits.Balance', 0);
            $credits = (int)val('Credits.Balance', $creditsMeta, 0);
            // Calculate level based on credits (simple formula)
            $level = min(6, max(1, floor($credits / 100) + 1));
        }
        $sender->setData('Level', $level);
        $sender->setData('Credits', $credits);

        // Get discussion and comment counts
        $sender->setData('DiscussionCount', val('CountDiscussions', $user, 0));
        $sender->setData('CommentCount', val('CountComments', $user, 0));

        // Get content based on tab
        $offset = 0;
        $limit = 20;
        $page = Gdn::request()->get('page', 1);
        $offset = ($page - 1) * $limit;

        switch ($tab) {
            case 'thread':
                // User's discussions (topics)
                $discussionModel = new DiscussionModel();
                $discussions = $discussionModel->getByUser($userID, $limit, $offset, false, Gdn::session()->UserID);
                $sender->setData('Discussions', $discussions);
                $sender->setData('TotalCount', $user->CountDiscussions);
                break;

            case 'post':
                // User's comments (replies)
                $commentModel = new CommentModel();
                $comments = $commentModel->getByUser($userID, $limit, $offset);
                $sender->setData('Comments', $comments);
                $sender->setData('TotalCount', $user->CountComments);
                break;

            case 'favorite':
                // User's bookmarks (if viewing own profile and logged in)
                if (Gdn::session()->UserID == $userID) {
                    $discussionModel = new DiscussionModel();
                    // Get bookmarked discussions with proper user filter
                    $bookmarks = $discussionModel->get($offset, $limit, [
                        'w.Bookmarked' => 1,
                        'w.UserID' => $userID
                    ]);
                    $sender->setData('Bookmarks', $bookmarks);
                    // Get total bookmark count for pagination
                    $bookmarkCount = val('CountBookmarks', $user, 0);
                    $sender->setData('TotalCount', $bookmarkCount);
                } else {
                    $sender->setData('Bookmarks', []);
                    $sender->setData('TotalCount', 0);
                }
                break;

            case 'general':
            default:
                // General overview - get recent activity
                $discussionModel = new DiscussionModel();
                $recentDiscussions = $discussionModel->getByUser($userID, 5, 0, false, Gdn::session()->UserID);
                $sender->setData('RecentDiscussions', $recentDiscussions);

                $commentModel = new CommentModel();
                $recentComments = $commentModel->getByUser($userID, 5, 0);
                $sender->setData('RecentComments', $recentComments);
                break;
        }

        // Build pagination
        $totalCount = $sender->data('TotalCount', 0);
        if ($totalCount > 0) {
            $sender->setData('CurrentPage', $page);
            $sender->setData('TotalPages', ceil($totalCount / $limit));
        }

        // Render
        $sender->render('space', '', 'themes/bitsmesh');
    }

    /**
     * User follow/followers list page - /space/{userid}/follow/{tab}
     *
     * Modern forum style follow list with tab navigation.
     *
     * @param ProfileController $sender The controller instance.
     * @param int $userID User ID to display.
     * @param string $tab Current tab (following, followers).
     * @return void
     */
    public function profileController_follow_create($sender, $userID = 0, $tab = 'following') {
        $userID = (int)$userID;

        // Validate user ID
        if ($userID <= 0) {
            throw notFoundException('User');
        }

        // Get user data
        $userModel = new UserModel();
        $user = $userModel->getID($userID, DATASET_TYPE_ARRAY);

        if (!$user) {
            throw notFoundException('User');
        }

        // Convert to object for easier access
        $user = (object)$user;

        // Load UserFollowModel
        require_once PATH_THEMES . '/bitsmesh/models/class.userfollowmodel.php';
        $followModel = UserFollowModel::instance();

        // Set up the page
        $sender->setData('User', $user);
        $sender->setData('UserID', $userID);
        $sender->setData('Tab', strtolower($tab));

        // Page title
        $titleKey = ($tab === 'followers') ? '%s\'s Followers' : '%s\'s Following';
        $sender->title(sprintf(t($titleKey), htmlspecialchars($user->Name)));

        // Add CSS
        $sender->addCssFile('bits-space.css', 'themes/bitsmesh');

        // Get counts
        $followingCount = $followModel->getFollowingCount($userID);
        $followersCount = $followModel->getFollowersCount($userID);
        $sender->setData('FollowingCount', $followingCount);
        $sender->setData('FollowersCount', $followersCount);

        // Pagination
        $limit = 20;
        $page = (int)Gdn::request()->get('page', 1);
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        // Get users based on tab
        if ($tab === 'followers') {
            $users = $followModel->getFollowers($userID, $limit, $offset);
            $totalCount = $followersCount;
        } else {
            $users = $followModel->getFollowing($userID, $limit, $offset);
            $totalCount = $followingCount;
        }

        $sender->setData('Users', $users);
        $sender->setData('TotalCount', $totalCount);
        $sender->setData('CurrentPage', $page);
        $sender->setData('TotalPages', $totalCount > 0 ? ceil($totalCount / $limit) : 1);

        // Render
        $sender->render('follow', '', 'themes/bitsmesh');
    }

    /**
     * AJAX endpoint for follow/unfollow toggle - /profile/togglefollow.json
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    public function profileController_toggleFollow_create($sender) {
        // Require logged in user
        if (!Gdn::session()->isValid()) {
            throw permissionException('SignedIn');
        }

        // CSRF protection
        $transientKey = Gdn::request()->get('TransientKey', Gdn::request()->post('TransientKey'));
        if (!Gdn::session()->validateTransientKey($transientKey)) {
            throw new Gdn_UserException(t('Invalid CSRF token'));
        }

        // Get target user ID
        $followUserID = (int)Gdn::request()->getValue('UserID', 0);

        if ($followUserID <= 0) {
            throw new Gdn_UserException(t('Invalid user'));
        }

        // Cannot follow yourself
        if ($followUserID === Gdn::session()->UserID) {
            throw new Gdn_UserException(t('You cannot follow yourself'));
        }

        // Check if target user exists
        $userModel = new UserModel();
        $targetUser = $userModel->getID($followUserID);
        if (!$targetUser) {
            throw notFoundException('User');
        }

        // Load UserFollowModel
        require_once PATH_THEMES . '/bitsmesh/models/class.userfollowmodel.php';
        $followModel = UserFollowModel::instance();

        // Toggle follow
        $result = $followModel->toggle(Gdn::session()->UserID, $followUserID);

        // Return JSON response
        $sender->deliveryType(DELIVERY_TYPE_DATA);
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->setData('Success', true);
        $sender->setData('IsFollowing', $result['isFollowing']);
        $sender->setData('FollowingCount', $result['followingCount']);
        $sender->setData('FollowersCount', $result['followersCount']);
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * AJAX endpoint to get follow status - /profile/followstatus.json
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    public function profileController_followStatus_create($sender) {
        $followUserID = (int)Gdn::request()->getValue('UserID', 0);

        if ($followUserID <= 0) {
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->setData('Success', false);
            $sender->setData('Error', 'Invalid user');
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Load UserFollowModel
        require_once PATH_THEMES . '/bitsmesh/models/class.userfollowmodel.php';
        $followModel = UserFollowModel::instance();

        // Get status
        $isFollowing = false;
        if (Gdn::session()->isValid()) {
            $isFollowing = $followModel->isFollowing(Gdn::session()->UserID, $followUserID);
        }

        $followingCount = $followModel->getFollowingCount($followUserID);
        $followersCount = $followModel->getFollowersCount($followUserID);

        // Return JSON response
        $sender->deliveryType(DELIVERY_TYPE_DATA);
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->setData('Success', true);
        $sender->setData('IsFollowing', $isFollowing);
        $sender->setData('FollowingCount', $followingCount);
        $sender->setData('FollowersCount', $followersCount);
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * User setting page - /setting
     *
     * Modern forum style user settings page with tab navigation.
     * Requires login.
     *
     * @param ProfileController $sender The controller instance.
     * @param string $tab Current tab (introduction, security, contact, block, preference, homepage, extend).
     * @return void
     */
    public function profileController_setting_create($sender, $tab = 'introduction') {
        // Require login
        if (!Gdn::session()->isValid()) {
            // For API requests, return JSON error
            if ($tab === 'save' || $tab === 'avatar') {
                $sender->deliveryMethod(DELIVERY_METHOD_JSON);
                $sender->deliveryType(DELIVERY_TYPE_DATA);
                $sender->setData('Success', false);
                $sender->setData('Error', t('You must be signed in.'));
                $sender->render('blank', 'utility', 'dashboard');
                return;
            }
            redirectTo('/entry/signin?Target=' . urlencode('/setting'));
            return;
        }

        $sender->permission('Garden.SignIn.Allow');

        // Handle API endpoints
        if ($tab === 'save') {
            $this->handleProfileSave($sender);
            return;
        }

        if ($tab === 'avatar') {
            $this->handleAvatarUpload($sender);
            return;
        }

        if ($tab === 'password') {
            $this->handlePasswordChange($sender);
            return;
        }

        // Get current user
        $userID = Gdn::session()->UserID;
        $userModel = new UserModel();
        $user = $userModel->getID($userID);

        if (!$user) {
            throw notFoundException('User');
        }

        // Tab definitions
        $tabs = [
            'introduction' => ['label' => t('Profile Info', '个人信息'), 'icon' => 'user'],
            'security' => ['label' => t('Security', '安全'), 'icon' => 'protect'],
            'contact' => ['label' => t('Contact', '联系方式'), 'icon' => 'phone-telephone'],
            'block' => ['label' => t('Blocked Users', '屏蔽用户'), 'icon' => 'people-delete'],
            'preference' => ['label' => t('Preferences', '常用偏好'), 'icon' => 'config'],
            'homepage' => ['label' => t('Homepage Sections', '首页版块'), 'icon' => 'all-application'],
            'extend' => ['label' => t('Extensions', '论坛扩展'), 'icon' => 'puzzle'],
        ];

        // Validate tab
        if (!isset($tabs[$tab])) {
            $tab = 'introduction';
        }

        // Set data for view
        $sender->setData('User', $user);
        $sender->setData('UserID', $userID);
        $sender->setData('Tab', $tab);
        $sender->setData('Tabs', $tabs);

        // Always load IP records for security tab (all tabs render together now)
        $ipRecords = $this->getRecentIPs($userID, 5);
        $sender->setData('IPRecords', $ipRecords);

        // Page title
        $sender->title(t('Settings', '设置'));

        // Add CSS and JS
        $sender->addCssFile('bits-setting.css', 'themes/bitsmesh');

        // Add Cropper.js from CDN (only for introduction tab)
        if ($tab === 'introduction') {
            $sender->Head->addCss('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css');
            $sender->Head->addScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js');
        }

        $sender->addJsFile('bits-setting.js', 'themes/bitsmesh');

        // Render
        $sender->render('setting', '', 'themes/bitsmesh');
    }

    /**
     * Handle profile save (Bio, Signature, Readme).
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    private function handleProfileSave($sender) {
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        // Get current user
        $userID = Gdn::session()->UserID;

        // Get form values
        $bio = Gdn::request()->post('Bio', '');
        $signature = Gdn::request()->post('Signature', '');
        $readme = Gdn::request()->post('Readme', '');

        // Validate Bio length
        if (strlen($bio) > 255) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Bio must be 255 characters or less.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Sanitize inputs
        $bio = Gdn_Format::text($bio);
        // Signature and Readme support Markdown, store as-is but sanitize
        $signature = trim($signature);
        $readme = trim($readme);

        // Update user
        $userModel = new UserModel();
        $result = $userModel->save([
            'UserID' => $userID,
            'Bio' => $bio,
            'Signature' => $signature,
            'Readme' => $readme
        ]);

        if ($result) {
            $sender->setData('Success', true);
            $sender->setData('Message', t('Your profile has been updated.'));
        } else {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Failed to update profile.'));
        }

        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Handle avatar upload with cropping.
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    private function handleAvatarUpload($sender) {
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        // Get current user
        $userID = Gdn::session()->UserID;

        // Get base64 image data
        $avatarData = Gdn::request()->post('Avatar', '');

        if (empty($avatarData)) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('No image data provided.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Parse base64 data
        if (preg_match('/^data:image\/(png|jpe?g|gif);base64,(.+)$/i', $avatarData, $matches)) {
            $imageType = strtolower($matches[1]);
            $imageData = base64_decode($matches[2]);

            if ($imageData === false) {
                $sender->setData('Success', false);
                $sender->setData('Error', t('Invalid image data.'));
                $sender->render('blank', 'utility', 'dashboard');
                return;
            }

            // Generate filename
            $ext = ($imageType === 'jpeg' || $imageType === 'jpg') ? 'jpg' : $imageType;
            $filename = 'userpics/np' . md5($userID . time() . rand()) . '.' . $ext;
            $targetPath = PATH_UPLOADS . '/' . $filename;

            // Ensure directory exists
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save image
            if (file_put_contents($targetPath, $imageData) === false) {
                $sender->setData('Success', false);
                $sender->setData('Error', t('Failed to save image.'));
                $sender->render('blank', 'utility', 'dashboard');
                return;
            }

            // Update user photo
            $userModel = new UserModel();
            $result = $userModel->save([
                'UserID' => $userID,
                'Photo' => $filename
            ]);

            if ($result) {
                $photoUrl = Gdn_Upload::url($filename);
                $sender->setData('Success', true);
                $sender->setData('PhotoUrl', $photoUrl);
                $sender->setData('Message', t('Avatar updated successfully.'));
            } else {
                // Clean up file on failure
                @unlink($targetPath);
                $sender->setData('Success', false);
                $sender->setData('Error', t('Failed to update avatar.'));
            }
        } else {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid image format.'));
        }

        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Handle password change API.
     *
     * POST /setting/password
     * Validates old password, then updates to new password.
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    private function handlePasswordChange($sender) {
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        // Verify CSRF token
        if (!Gdn::session()->validateTransientKey(Gdn::request()->post('TransientKey'))) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid request. Please refresh and try again.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get current user
        $userID = Gdn::session()->UserID;
        $userModel = new UserModel();
        $user = $userModel->getID($userID, DATASET_TYPE_ARRAY);

        if (!$user) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('User not found.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get form values
        $oldPassword = Gdn::request()->post('OldPassword', '');
        $newPassword = Gdn::request()->post('NewPassword', '');
        $confirmPassword = Gdn::request()->post('ConfirmPassword', '');

        // Validate old password
        $passwordHash = new Gdn_PasswordHash();
        if (!$passwordHash->checkPassword($oldPassword, $user['Password'], $user['HashMethod'] ?? 'Vanilla')) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Current password is incorrect.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Validate new password length (minimum 6 characters)
        if (strlen($newPassword) < 6) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('New password must be at least 6 characters.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Passwords do not match.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Update password
        $result = $userModel->save([
            'UserID' => $userID,
            'Password' => $newPassword
        ]);

        if ($result) {
            $sender->setData('Success', true);
            $sender->setData('Message', t('Password changed successfully.'));
        } else {
            $sender->setData('Success', false);
            $sender->setData('Error', t('Failed to change password. Please try again.'));
        }

        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Get recent login IP records for a user.
     *
     * @param int $userID User ID
     * @param int $limit Maximum number of records
     * @return array Array of IP records with decoded IP and last access time
     */
    private function getRecentIPs($userID, $limit = 5) {
        $ipRecords = Gdn::sql()
            ->select('IPAddress, DateUpdated')
            ->from('UserIP')
            ->where('UserID', $userID)
            ->orderBy('DateUpdated', 'desc')
            ->limit($limit)
            ->get()
            ->resultArray();

        // Decode IP addresses
        foreach ($ipRecords as &$record) {
            $record['IPAddress'] = ipDecode($record['IPAddress']);
        }

        return $ipRecords;
    }

    /**
     * Notification center page - /notification
     *
     * Modern forum style notification page with tabs:
     * - @Me (mentions)
     * - Reply (topic replies)
     * - Message (private messages)
     *
     * @param ProfileController $sender The controller instance.
     * @param string $tab Current tab (atMe, reply, message).
     * @return void
     */
    public function profileController_notification_create($sender, $tab = '') {
        // Require login
        if (!Gdn::session()->isValid()) {
            redirectTo('/entry/signin?Target=' . urlencode('/notification'));
            return;
        }

        $sender->permission('Garden.SignIn.Allow');

        $userID = Gdn::session()->UserID;

        // Determine current tab from hash or parameter
        // Default to 'atMe'
        $tab = strtolower($tab) ?: 'atme';
        if (!in_array($tab, ['atme', 'reply', 'message'])) {
            $tab = 'atme';
        }

        // Get Activity data
        $activityModel = new ActivityModel();

        // Vanilla stores @mentions in Comment/Discussion ActivityTypes
        // The actual reason is stored in Data JSON field as "Reason":["mention"]
        // So we need to get all notifications and filter by Reason

        // Get Comment and Discussion activity types
        $commentType = ActivityModel::getActivityType('Comment');
        $discussionType = ActivityModel::getActivityType('Discussion');
        $discCommentType = ActivityModel::getActivityType('DiscussionComment');

        $notificationTypeIDs = [];
        if ($commentType) {
            $notificationTypeIDs[] = val('ActivityTypeID', $commentType);
        }
        if ($discussionType) {
            $notificationTypeIDs[] = val('ActivityTypeID', $discussionType);
        }
        if ($discCommentType) {
            $notificationTypeIDs[] = val('ActivityTypeID', $discCommentType);
        }

        // Also include legacy mention types
        $discMentionType = ActivityModel::getActivityType('DiscussionMention');
        $commentMentionType = ActivityModel::getActivityType('CommentMention');
        if ($discMentionType) {
            $notificationTypeIDs[] = val('ActivityTypeID', $discMentionType);
        }
        if ($commentMentionType) {
            $notificationTypeIDs[] = val('ActivityTypeID', $commentMentionType);
        }

        // Get all notifications for the user
        $allActivities = [];
        if (!empty($notificationTypeIDs)) {
            $allActivities = $activityModel->getWhere([
                'NotifyUserID' => $userID,
                'ActivityTypeID' => $notificationTypeIDs
            ], '', '', 100)->resultArray();
        }

        // Separate @mentions and replies based on Data.Reason field
        $mentions = [];
        $replies = [];

        foreach ($allActivities as $activity) {
            $data = val('Data', $activity);

            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            $reasons = is_array($data) ? val('Reason', $data, []) : [];
            if (!is_array($reasons)) {
                $reasons = [$reasons];
            }

            // Check if this is a mention notification
            if (in_array('mention', $reasons)) {
                $mentions[] = $activity;
            }
            // Check if this is a reply to my topic (mine = someone replied to my discussion)
            elseif (in_array('mine', $reasons) || val('ActivityTypeID', $activity) == val('ActivityTypeID', $discCommentType)) {
                $replies[] = $activity;
            }
        }

        // Process activities: add user info, format dates, check read status
        $userModel = new UserModel();
        $userCache = [];

        foreach ($mentions as &$activity) {
            $activity = $this->processActivity($activity, $userModel, $userCache);
        }
        foreach ($replies as &$activity) {
            $activity = $this->processActivity($activity, $userModel, $userCache);
        }

        // Get Conversations
        $conversations = [];
        $unreadConversations = 0;

        // Check if Conversations application is enabled
        if (Gdn::applicationManager()->checkApplication('Conversations')) {
            // Ensure ConversationModel class is loaded
            if (!class_exists('ConversationModel')) {
                require_once PATH_APPLICATIONS . '/conversations/models/class.conversationmodel.php';
            }
            $conversationModel = new ConversationModel();
            $inboxResult = $conversationModel->getInbox($userID, 50);
            // Handle both DataSet and array returns
            $conversations = is_object($inboxResult) && method_exists($inboxResult, 'resultArray')
                ? $inboxResult->resultArray()
                : (is_array($inboxResult) ? $inboxResult : []);

            // Join participants to get other user info
            if (!empty($conversations)) {
                $conversationModel->joinParticipants($conversations, 10, ['Name', 'Photo']);
            }

            // Process conversations: add user info
            foreach ($conversations as &$conv) {
                $conv = $this->processConversation($conv, $userModel, $userCache);
            }

            // Get unread conversation count
            $unreadConversations = (int)val('CountUnreadConversations', Gdn::session()->User, 0);
        }

        // Calculate unread counts
        $unreadMentions = 0;
        $unreadReplies = 0;

        // Notified = 3 (SENT_PENDING) means unread
        foreach ($mentions as $m) {
            if (val('Notified', $m) == ActivityModel::SENT_PENDING) {
                $unreadMentions++;
            }
        }
        foreach ($replies as $r) {
            if (val('Notified', $r) == ActivityModel::SENT_PENDING) {
                $unreadReplies++;
            }
        }

        // Set data for view
        $sender->setData('Tab', $tab);
        $sender->setData('Mentions', $mentions);
        $sender->setData('Replies', $replies);
        $sender->setData('Conversations', $conversations);
        $sender->setData('UnreadMentions', $unreadMentions);
        $sender->setData('UnreadReplies', $unreadReplies);
        $sender->setData('UnreadConversations', $unreadConversations);

        // Handle ?to=username parameter for starting new conversation
        $toUsername = Gdn::request()->get('to', '');
        $toUser = null;
        if (!empty($toUsername)) {
            $toUser = $userModel->getByUsername($toUsername);
            if ($toUser) {
                $sender->setData('ToUser', [
                    'UserID' => val('UserID', $toUser),
                    'Name' => val('Name', $toUser),
                    'Photo' => $this->getUserPhoto($toUser)
                ]);
                // Force message tab when ?to= is provided
                $tab = 'message';
                $sender->setData('Tab', $tab);
            }
        }

        // Page title
        $sender->title(t('Notifications'));

        // Add CSS
        $sender->addCssFile('bits-notification.css', 'themes/bitsmesh');

        // Render
        $sender->render('notification', '', 'themes/bitsmesh');
    }

    /**
     * Process activity for display.
     *
     * @param array $activity Activity data.
     * @param UserModel $userModel User model instance.
     * @param array &$userCache User cache array.
     * @return array Processed activity.
     */
    private function processActivity($activity, $userModel, &$userCache) {
        // Get activity user info
        $activityUserID = val('ActivityUserID', $activity, 0);
        if ($activityUserID && !isset($userCache[$activityUserID])) {
            $userCache[$activityUserID] = $userModel->getID($activityUserID, DATASET_TYPE_ARRAY);
        }

        $activityUser = isset($userCache[$activityUserID]) ? $userCache[$activityUserID] : null;

        if ($activityUser) {
            $activity['ActivityUserName'] = val('Name', $activityUser, '');
            $activity['ActivityUserPhoto'] = $this->getUserPhoto($activityUser);
            $activity['ActivityUserUrl'] = userUrl($activityUser);
        } else {
            $activity['ActivityUserName'] = t('Unknown');
            $activity['ActivityUserPhoto'] = UserModel::getDefaultAvatarUrl();
            $activity['ActivityUserUrl'] = '#';
        }

        // Format date
        $activity['DateInsertedFormatted'] = Gdn_Format::date(val('DateInserted', $activity), 'html');

        // Check if unread (Notified = SENT_PENDING = 3)
        $activity['IsUnread'] = (val('Notified', $activity) == ActivityModel::SENT_PENDING);

        // Parse headline for link and text
        $activity['HeadlineHtml'] = $this->formatActivityHeadline($activity);

        return $activity;
    }

    /**
     * Get user photo URL.
     *
     * @param array $user User data.
     * @return string Photo URL.
     */
    private function getUserPhoto($user) {
        $photo = val('Photo', $user, '');
        if ($photo) {
            if (!isUrl($photo)) {
                $photo = Gdn_Upload::url(changeBasename($photo, 'n%s'));
            }
        } else {
            $photo = UserModel::getDefaultAvatarUrl($user);
        }
        return $photo;
    }

    /**
     * Format activity headline for display.
     *
     * @param array $activity Activity data.
     * @return string HTML headline.
     */
    private function formatActivityHeadline($activity) {
        $headline = val('Headline', $activity, '');

        // If headline contains HTML, return as-is (sanitized)
        if (strpos($headline, '<') !== false) {
            return Gdn_Format::html($headline);
        }

        // Build basic headline
        $activityType = val('ActivityType', $activity, '');
        $userName = htmlspecialchars(val('ActivityUserName', $activity, ''));
        $userUrl = htmlspecialchars(val('ActivityUserUrl', $activity, '#'));

        $recordType = val('RecordType', $activity, '');
        $recordID = val('RecordID', $activity, 0);

        // Get story excerpt if available
        $story = val('Story', $activity, '');
        if (empty($story)) {
            $story = val('Route', $activity, '');
        }

        $excerpt = '';
        if ($story) {
            $excerpt = '<span class="notification-excerpt">' . htmlspecialchars(Gdn_Format::plainText(mb_substr($story, 0, 100))) . '</span>';
        }

        // Build action text based on type
        switch ($activityType) {
            case 'DiscussionMention':
                $actionText = t('mentioned you in a discussion');
                break;
            case 'CommentMention':
                $actionText = t('mentioned you in a comment');
                break;
            case 'DiscussionComment':
                $actionText = t('replied to your discussion');
                break;
            default:
                $actionText = t('sent you a notification');
        }

        return sprintf(
            '<a href="%s" class="notification-user">%s</a> <span class="notification-action">%s</span>%s',
            $userUrl,
            $userName,
            $actionText,
            $excerpt ? '<br>' . $excerpt : ''
        );
    }

    /**
     * Process conversation for display.
     *
     * @param array $conv Conversation data.
     * @param UserModel $userModel User model instance.
     * @param array &$userCache User cache array.
     * @return array Processed conversation.
     */
    private function processConversation($conv, $userModel, &$userCache) {
        // Get the participants (joined by ConversationModel->joinParticipants)
        $participants = val('Participants', $conv, []);
        $currentUserID = Gdn::session()->UserID;

        // Find the other user (not current user)
        $otherUser = null;
        foreach ($participants as $participant) {
            $uid = val('UserID', $participant);
            if ($uid && $uid != $currentUserID) {
                $otherUser = $participant;
                break;
            }
        }

        if ($otherUser) {
            $conv['OtherUserName'] = val('Name', $otherUser, '');
            $conv['OtherUserPhoto'] = $this->getUserPhoto($otherUser);
            $conv['OtherUserUrl'] = userUrl($otherUser);
        } else {
            $conv['OtherUserName'] = t('Unknown');
            $conv['OtherUserPhoto'] = UserModel::getDefaultAvatarUrl();
            $conv['OtherUserUrl'] = '#';
        }

        // Check for multiple participants (more than 2 means group chat)
        $conv['IsGroup'] = count($participants) > 2;

        // Get last message info
        $conv['LastMessageFormatted'] = Gdn_Format::date(val('DateLastMessage', $conv), 'html');

        // Get last message body for preview
        $conv['LastBody'] = val('LastMessage', $conv, '');

        // Check if has unread
        $conv['HasUnread'] = (val('CountNewMessages', $conv, 0) > 0);

        return $conv;
    }

    /**
     * Mark notifications as read - POST /profile/marknotificationsread
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    public function profileController_markNotificationsRead_create($sender) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('You must be signed in.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $sender->permission('Garden.SignIn.Allow');

        // CSRF protection
        if (!Gdn::session()->validateTransientKey(Gdn::request()->post('TransientKey'))) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid request.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $userID = Gdn::session()->UserID;
        $type = Gdn::request()->post('type', 'all');

        // Determine which activity types to mark as read
        $activityTypes = [];
        if ($type === 'mentions' || $type === 'all') {
            $activityTypes = array_merge($activityTypes, ['DiscussionMention', 'CommentMention']);
        }
        if ($type === 'replies' || $type === 'all') {
            $activityTypes[] = 'DiscussionComment';
        }

        if (!empty($activityTypes)) {
            // Get unread activities and mark them as read
            $activityModel = new ActivityModel();

            // Get activity IDs to mark as read
            $activities = Gdn::sql()
                ->select('ActivityID')
                ->from('Activity')
                ->where('NotifyUserID', $userID)
                ->whereIn('ActivityType', $activityTypes)
                ->where('Notified', ActivityModel::SENT_PENDING)
                ->get()
                ->resultArray();

            $activityIDs = array_column($activities, 'ActivityID');

            if (!empty($activityIDs)) {
                // Mark as read (Notified = SENT_OK = 2)
                Gdn::sql()
                    ->update('Activity')
                    ->set('Notified', ActivityModel::SENT_OK)
                    ->whereIn('ActivityID', $activityIDs)
                    ->put();
            }
        }

        // Update user's notification count
        $this->updateUserNotificationCount($userID);

        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);
        $sender->setData('Success', true);
        $sender->setData('Message', t('Notifications marked as read.'));
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Update user's notification count.
     *
     * @param int $userID User ID.
     * @return void
     */
    private function updateUserNotificationCount($userID) {
        // Count remaining unread notifications
        $count = Gdn::sql()
            ->select('ActivityID', 'COUNT', 'Count')
            ->from('Activity')
            ->where('NotifyUserID', $userID)
            ->where('Notified', ActivityModel::SENT_PENDING)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);

        $unreadCount = val('Count', $count, 0);

        // Update user record
        Gdn::sql()
            ->update('User')
            ->set('CountNotifications', $unreadCount)
            ->where('UserID', $userID)
            ->put();

        // Update session
        if (Gdn::session()->User) {
            Gdn::session()->User->CountNotifications = $unreadCount;
        }
    }

    /**
     * Get conversation messages - GET /profile/conversationmessages/{ConversationID}
     *
     * Returns HTML fragment for chat panel.
     *
     * @param ProfileController $sender The controller instance.
     * @param int $conversationID Conversation ID.
     * @return void
     */
    public function profileController_conversationMessages_create($sender, $conversationID = 0) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('You must be signed in.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $sender->permission('Garden.SignIn.Allow');

        // Check if Conversations application is enabled
        if (!Gdn::applicationManager()->checkApplication('Conversations')) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Conversations are not enabled.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $conversationID = (int)$conversationID;
        $userID = Gdn::session()->UserID;

        if ($conversationID <= 0) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid conversation.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Verify user is participant
        $conversationModel = new ConversationModel();
        $conversation = $conversationModel->getID($conversationID, DATASET_TYPE_ARRAY);

        if (!$conversation) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Conversation not found.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Check if user is a participant
        $isParticipant = Gdn::sql()
            ->select('UserID')
            ->from('UserConversation')
            ->where('ConversationID', $conversationID)
            ->where('UserID', $userID)
            ->get()
            ->firstRow();

        if (!$isParticipant) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('You are not a participant in this conversation.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get messages
        $messageModel = new ConversationMessageModel();
        $messages = $messageModel->getRecent($conversationID, $userID, 0, 50)->resultArray();

        // Process messages
        $userModel = new UserModel();
        $userCache = [];

        foreach ($messages as &$msg) {
            $insertUserID = val('InsertUserID', $msg, 0);
            if ($insertUserID && !isset($userCache[$insertUserID])) {
                $userCache[$insertUserID] = $userModel->getID($insertUserID, DATASET_TYPE_ARRAY);
            }

            $msgUser = isset($userCache[$insertUserID]) ? $userCache[$insertUserID] : null;

            if ($msgUser) {
                $msg['InsertUserName'] = val('Name', $msgUser, '');
                $msg['InsertUserPhoto'] = $this->getUserPhoto($msgUser);
                $msg['InsertUserUrl'] = userUrl($msgUser);
            } else {
                $msg['InsertUserName'] = t('Unknown');
                $msg['InsertUserPhoto'] = UserModel::getDefaultAvatarUrl();
                $msg['InsertUserUrl'] = '#';
            }

            // Check if sent by current user
            $msg['IsSent'] = ($insertUserID == $userID);

            // Format message body
            $msg['BodyFormatted'] = Gdn_Format::to(val('Body', $msg, ''), val('Format', $msg, 'Text'));

            // Format date
            $msg['DateInsertedFormatted'] = Gdn_Format::date(val('DateInserted', $msg), 'html');
        }

        // Mark conversation as read
        $conversationModel->markRead($conversationID, $userID);

        // Get all participants to find the other user's name
        $participants = Gdn::sql()
            ->select('uc.UserID')
            ->from('UserConversation uc')
            ->where('uc.ConversationID', $conversationID)
            ->get()
            ->resultArray();

        $otherUserName = '';
        foreach ($participants as $participant) {
            $participantUserID = val('UserID', $participant, 0);
            if ($participantUserID != $userID) {
                // Get the other user's info
                if (isset($userCache[$participantUserID])) {
                    $otherUser = $userCache[$participantUserID];
                } else {
                    $otherUser = $userModel->getID($participantUserID, DATASET_TYPE_ARRAY);
                }
                if ($otherUser) {
                    $otherUserName = val('Name', $otherUser, '');
                }
                break;
            }
        }

        // Add other user name to conversation data
        $conversation['OtherUserName'] = $otherUserName;

        // Set data
        $sender->setData('Conversation', $conversation);
        $sender->setData('Messages', $messages);

        // Render only the view content (no master template) for AJAX requests
        $sender->deliveryType(DELIVERY_TYPE_VIEW);
        $sender->render('conversation_messages', '', 'themes/bitsmesh');
    }

    /**
     * Send message to conversation - POST /profile/sendmessage
     *
     * @param ProfileController $sender The controller instance.
     * @return void
     */
    public function profileController_sendMessage_create($sender) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('You must be signed in.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $sender->permission('Garden.SignIn.Allow');

        // CSRF protection
        if (!Gdn::session()->validateTransientKey(Gdn::request()->post('TransientKey'))) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid request.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Check if Conversations application is enabled
        if (!Gdn::applicationManager()->checkApplication('Conversations')) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Conversations are not enabled.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        $conversationID = (int)Gdn::request()->post('ConversationID', 0);
        $body = trim(Gdn::request()->post('Body', ''));
        $userID = Gdn::session()->UserID;

        if ($conversationID <= 0) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Invalid conversation.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        if (empty($body)) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Message cannot be empty.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Verify user is participant
        $isParticipant = Gdn::sql()
            ->select('UserID')
            ->from('UserConversation')
            ->where('ConversationID', $conversationID)
            ->where('UserID', $userID)
            ->get()
            ->firstRow();

        if (!$isParticipant) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('You are not a participant in this conversation.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Add message
        $messageModel = new ConversationMessageModel();
        $messageID = $messageModel->save([
            'ConversationID' => $conversationID,
            'Body' => $body,
            'Format' => 'Text'
        ]);

        if ($messageID) {
            // Get the new message for display
            $message = $messageModel->getID($messageID, DATASET_TYPE_ARRAY);

            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', true);
            $sender->setData('MessageID', $messageID);
            $sender->setData('Message', $message);
        } else {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', t('Failed to send message.'));
        }

        $sender->render('blank', 'utility', 'dashboard');
    }

    // ==========================================================================
    // Comment Reactions & Chicken Leg API Endpoints
    // ==========================================================================

    /**
     * React to a comment (like/dislike) - POST /discussion/reactcomment
     *
     * Request params:
     * - CommentID: int (required)
     * - Score: int (1 = like, -1 = dislike)
     * - TransientKey: string (CSRF token)
     *
     * Response:
     * - Success: bool
     * - Action: string (added, removed, switched)
     * - NewScore: int (user's new reaction score)
     * - LikeCount: int
     * - DislikeCount: int
     *
     * @param DiscussionController $sender The controller instance.
     * @return void
     */
    public function discussionController_reactComment_create($sender) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'NotLoggedIn');
            $sender->setData('Message', t('You must be signed in to react.', '请先登录'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // CSRF protection
        $transientKey = Gdn::request()->get('TransientKey', Gdn::request()->post('TransientKey'));
        if (!Gdn::session()->validateTransientKey($transientKey)) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidToken');
            $sender->setData('Message', t('Invalid request.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get parameters
        $commentID = (int)Gdn::request()->getValue('CommentID', 0);
        $score = (int)Gdn::request()->getValue('Score', 0);

        // Validate
        if ($commentID <= 0) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidComment');
            $sender->setData('Message', t('Invalid comment.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        if (!in_array($score, [1, -1])) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidScore');
            $sender->setData('Message', t('Invalid reaction type.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Verify comment exists
        $commentModel = new CommentModel();
        $comment = $commentModel->getID($commentID);
        if (!$comment) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'CommentNotFound');
            $sender->setData('Message', t('Comment not found.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Load model and set reaction
        require_once PATH_THEMES . '/bitsmesh/models/class.usercommentmodel.php';
        $userCommentModel = UserCommentModel::instance();
        $result = $userCommentModel->setReaction(Gdn::session()->UserID, $commentID, $score);

        // Return response
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);
        $sender->setData('Success', $result['success']);
        if ($result['success']) {
            $sender->setData('Action', $result['action']);
            $sender->setData('NewScore', $result['newScore']);
            $sender->setData('LikeCount', $result['likeCount']);
            $sender->setData('DislikeCount', $result['dislikeCount']);
        } else {
            $sender->setData('Error', $result['error']);
        }
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * React to content (Discussion or Comment) - POST /discussion/reactcontent
     *
     * Unified API for handling like/dislike reactions on both discussions and comments.
     *
     * Request params:
     * - RecordType: string ('Discussion' or 'Comment')
     * - RecordID: int
     * - Score: int (1 = like, -1 = dislike)
     * - TransientKey: string (CSRF token)
     *
     * Response:
     * - Success: bool
     * - Action: string (added, removed, switched)
     * - NewScore: int (user's new reaction score)
     * - LikeCount: int
     * - DislikeCount: int
     *
     * @param DiscussionController $sender The controller instance.
     * @return void
     */
    public function discussionController_reactContent_create($sender) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'NotLoggedIn');
            $sender->setData('Message', t('You must be signed in to react.', '请先登录'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // CSRF protection
        $transientKey = Gdn::request()->get('TransientKey', Gdn::request()->post('TransientKey'));
        if (!Gdn::session()->validateTransientKey($transientKey)) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidToken');
            $sender->setData('Message', t('Invalid request.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get parameters
        $recordType = trim(Gdn::request()->getValue('RecordType', ''));
        $recordID = (int)Gdn::request()->getValue('RecordID', 0);
        $score = (int)Gdn::request()->getValue('Score', 0);

        // Validate record type
        $recordType = ucfirst(strtolower($recordType));
        if (!in_array($recordType, ['Discussion', 'Comment'])) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidRecordType');
            $sender->setData('Message', t('Invalid record type.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Validate record ID
        if ($recordID <= 0) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidRecordID');
            $sender->setData('Message', t('Invalid record.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Validate score
        if (!in_array($score, [1, -1])) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidScore');
            $sender->setData('Message', t('Invalid reaction type.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Load model and set reaction
        require_once PATH_THEMES . '/bitsmesh/models/class.userreactionmodel.php';
        $userReactionModel = UserReactionModel::instance();
        $result = $userReactionModel->setReaction(Gdn::session()->UserID, $recordType, $recordID, $score);

        // Return response
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);
        $sender->setData('Success', $result['success']);
        if ($result['success']) {
            $sender->setData('Action', $result['action']);
            $sender->setData('NewScore', $result['newScore']);
            $sender->setData('LikeCount', $result['likeCount']);
            $sender->setData('DislikeCount', $result['dislikeCount']);
        } else {
            $sender->setData('Error', $result['error']);
            $sender->setData('Message', t('Failed to save reaction.'));
        }
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     *
     * Request params:
     * - RecordType: string ('Discussion' or 'Comment')
     * - RecordID: int
     * - TransientKey: string (CSRF token)
     *
     * Response:
     * - Success: bool
     * - NewCount: int (new chicken leg count)
     * - RemainingQuota: int (user's remaining daily quota)
     * - Message: string
     *
     * @param DiscussionController $sender The controller instance.
     * @return void
     */
    public function discussionController_giveChickenLeg_create($sender) {
        // Require login
        if (!Gdn::session()->isValid()) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'NotLoggedIn');
            $sender->setData('Message', t('You must be signed in.', '请先登录'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // CSRF protection
        $transientKey = Gdn::request()->get('TransientKey', Gdn::request()->post('TransientKey'));
        if (!Gdn::session()->validateTransientKey($transientKey)) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidToken');
            $sender->setData('Message', t('Invalid request.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Get parameters
        $recordType = trim(Gdn::request()->getValue('RecordType', ''));
        $recordID = (int)Gdn::request()->getValue('RecordID', 0);

        // Validate record type
        $recordType = ucfirst(strtolower($recordType));
        if (!in_array($recordType, ['Discussion', 'Comment'])) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidRecordType');
            $sender->setData('Message', t('Invalid record type.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Validate record ID
        if ($recordID <= 0) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidRecordID');
            $sender->setData('Message', t('Invalid record.'));
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        // Load model and give chicken leg
        try {
            require_once PATH_THEMES . '/bitsmesh/models/class.chickenlegmodel.php';
            $chickenLegModel = ChickenLegModel::instance();
            $result = $chickenLegModel->giveChickenLeg(Gdn::session()->UserID, $recordType, $recordID);

            // Return response
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', $result['success']);
            if ($result['success']) {
                $sender->setData('NewCount', $result['newCount']);
                $sender->setData('RemainingQuota', $result['remainingQuota']);
                $sender->setData('Message', $result['message']);
            } else {
                $sender->setData('Error', $result['error']);
                $sender->setData('Message', $result['message'] ?? t('Failed to give chicken leg.'));
                if (isset($result['remainingQuota'])) {
                    $sender->setData('RemainingQuota', $result['remainingQuota']);
                }
            }
            $sender->render('blank', 'utility', 'dashboard');
        } catch (Exception $e) {
            $sender->deliveryMethod(DELIVERY_METHOD_JSON);
            $sender->deliveryType(DELIVERY_TYPE_DATA);
            $sender->setData('Success', false);
            $sender->setData('Error', 'Exception');
            $sender->setData('Message', t('An error occurred. Please try again.'));
            $sender->render('blank', 'utility', 'dashboard');
        }
    }

    /**
     * Get user's remaining chicken leg quota - GET /discussion/chickenLegQuota.json
     *
     * Response:
     * - RemainingQuota: int
     * - DailyQuota: int (total daily quota)
     *
     * @param DiscussionController $sender The controller instance.
     * @return void
     */
    public function discussionController_chickenLegQuota_create($sender) {
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        if (!Gdn::session()->isValid()) {
            $sender->setData('RemainingQuota', 0);
            $sender->setData('DailyQuota', 1);
            $sender->setData('LoggedIn', false);
        } else {
            require_once PATH_THEMES . '/bitsmesh/models/class.chickenlegmodel.php';
            $chickenLegModel = ChickenLegModel::instance();
            $sender->setData('RemainingQuota', $chickenLegModel->getRemainingQuota(Gdn::session()->UserID));
            $sender->setData('DailyQuota', ChickenLegModel::DAILY_FREE_QUOTA);
            $sender->setData('LoggedIn', true);
        }

        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Get comment reaction data for a discussion - GET /discussion/commentReactions.json
     *
     * Request params:
     * - DiscussionID: int
     *
     * Response:
     * - Reactions: array (CommentID => ['likeCount', 'dislikeCount', 'userScore'])
     *
     * @param DiscussionController $sender The controller instance.
     * @return void
     */
    public function discussionController_commentReactions_create($sender) {
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $sender->deliveryType(DELIVERY_TYPE_DATA);

        $discussionID = (int)Gdn::request()->getValue('DiscussionID', 0);

        if ($discussionID <= 0) {
            $sender->setData('Success', false);
            $sender->setData('Error', 'InvalidDiscussion');
            $sender->render('blank', 'utility', 'dashboard');
            return;
        }

        require_once PATH_THEMES . '/bitsmesh/models/class.userreactionmodel.php';
        require_once PATH_THEMES . '/bitsmesh/models/class.chickenlegmodel.php';
        $userReactionModel = UserReactionModel::instance();
        $chickenLegModel = ChickenLegModel::instance();

        // Get reactions for discussion page (includes discussion + all comments)
        $userID = Gdn::session()->isValid() ? Gdn::session()->UserID : null;
        $reactions = $userReactionModel->getDiscussionPageReactions($discussionID, $userID);

        // Add chicken leg counts to reactions
        // Get discussion chicken leg count
        $discussionChickenCount = $chickenLegModel->getChickenLegCount('Discussion', $discussionID);
        $reactions['discussion']['chickenLegCount'] = $discussionChickenCount;

        // Get comment IDs and their chicken leg counts
        $commentIDs = array_keys($reactions);
        $commentIDs = array_filter($commentIDs, function($key) {
            return $key !== 'discussion';
        });
        if (!empty($commentIDs)) {
            $chickenCounts = $chickenLegModel->getChickenLegCounts('Comment', $commentIDs);
            foreach ($chickenCounts as $commentID => $count) {
                if (isset($reactions[$commentID])) {
                    $reactions[$commentID]['chickenLegCount'] = $count;
                }
            }
        }

        $sender->setData('Success', true);
        $sender->setData('Reactions', $reactions);
        $sender->render('blank', 'utility', 'dashboard');
    }

    /**
     * Update CountBookmarks on Discussion table after bookmark operation.
     *
     * The Vanilla bookmark() method doesn't update the Discussion.CountBookmarks field,
     * so we hook into AfterBookmark to do it ourselves.
     *
     * @param DiscussionModel $sender The model instance.
     * @param array $args Event arguments containing DiscussionID, UserID, Bookmarked.
     * @return void
     */
    public function discussionModel_afterBookmark_handler($sender, $args) {
        $discussionID = (int)val('DiscussionID', $args, 0);
        if ($discussionID <= 0) {
            return;
        }

        // Count total bookmarks for this discussion
        $bookmarkCount = Gdn::sql()
            ->select('DiscussionID', 'count', 'Count')
            ->from('UserDiscussion')
            ->where('DiscussionID', $discussionID)
            ->where('Bookmarked', 1)
            ->get()
            ->firstRow();

        $count = $bookmarkCount ? (int)$bookmarkCount->Count : 0;

        // Update Discussion.CountBookmarks
        Gdn::sql()
            ->update('Discussion')
            ->set('CountBookmarks', $count)
            ->where('DiscussionID', $discussionID)
            ->put();
    }
}
