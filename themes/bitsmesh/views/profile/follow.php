<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh User Follow List View
 *
 * Modern forum style follow/followers list with tab navigation.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

$user = $this->data('User');
$userID = $this->data('UserID');
$tab = $this->data('Tab', 'following');
$users = $this->data('Users', []);
$totalCount = $this->data('TotalCount', 0);
$currentPage = $this->data('CurrentPage', 1);
$totalPages = $this->data('TotalPages', 1);

// Get user photo
$photoUrl = userPhotoUrl($user);

// Base URL for tabs
$baseUrl = '/space/' . $userID . '/follow';

// Tab definitions
$tabs = [
    'following' => ['label' => t('Following', '关注'), 'icon' => 'star', 'count' => $this->data('FollowingCount', 0)],
    'followers' => ['label' => t('Followers', '粉丝'), 'icon' => 'concern', 'count' => $this->data('FollowersCount', 0)],
];
?>

<div class="bits-space-page">
    <!-- Head Container: Avatar + User Info -->
    <div class="bits-space-head">
        <a href="<?php echo url('/space/' . $userID); ?>">
            <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($user->Name); ?>" class="bits-space-avatar">
        </a>
        <div class="bits-space-info">
            <h1 class="bits-space-username">
                <a href="<?php echo url('/space/' . $userID); ?>" style="color: inherit; text-decoration: none;">
                    <?php echo htmlspecialchars($user->Name); ?>
                </a>
            </h1>
            <p class="bits-space-bio"><?php echo t('Follow List', '关注列表'); ?></p>
        </div>
        <div class="bits-space-actions">
            <a href="<?php echo url('/space/' . $userID); ?>" class="bits-btn bits-btn-message">
                <?php echo t('Back to Space', '返回空间'); ?>
            </a>
        </div>
    </div>

    <!-- Tab Selector -->
    <div class="bits-space-selector">
        <?php foreach ($tabs as $tabKey => $tabInfo): ?>
        <a href="<?php echo url($baseUrl . '/' . $tabKey); ?>" class="bits-select-item <?php echo ($tab === $tabKey) ? 'active' : ''; ?>">
            <svg class="iconpark-icon"><use href="#<?php echo $tabInfo['icon']; ?>"></use></svg>
            <span><?php echo $tabInfo['label']; ?> (<?php echo number_format($tabInfo['count']); ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Content Area -->
    <div class="bits-space-content">
        <div class="bits-space-section">
            <?php if ($users && (is_array($users) ? count($users) > 0 : $users->numRows() > 0)): ?>
            <div class="bits-follow-list">
                <?php foreach ($users as $followUser): ?>
                <?php
                    // Get user photo URL
                    $followPhoto = val('Photo', $followUser, '');
                    if ($followPhoto && !isUrl($followPhoto)) {
                        $followPhoto = Gdn_Upload::url(changeBasename($followPhoto, 'n%s'));
                    } elseif (!$followPhoto) {
                        $followPhoto = UserModel::getDefaultAvatarUrl($followUser);
                    }

                    $followUserID = val('UserID', $followUser);
                    $followName = val('Name', $followUser);
                    $followDate = val('FollowDate', $followUser);

                    // Check if current user is following this user
                    $isFollowing = false;
                    if (Gdn::session()->UserID) {
                        $followModel = UserFollowModel::instance();
                        $isFollowing = $followModel->isFollowing(Gdn::session()->UserID, $followUserID);
                    }
                ?>
                <div class="bits-follow-item" data-userid="<?php echo $followUserID; ?>">
                    <a href="<?php echo url('/space/' . $followUserID); ?>" class="bits-follow-avatar">
                        <img src="<?php echo htmlspecialchars($followPhoto); ?>" alt="<?php echo htmlspecialchars($followName); ?>">
                    </a>
                    <div class="bits-follow-info">
                        <a href="<?php echo url('/space/' . $followUserID); ?>" class="bits-follow-name">
                            <?php echo htmlspecialchars($followName); ?>
                        </a>
                        <span class="bits-follow-date">
                            <?php echo Gdn_Format::date($followDate, 'html'); ?>
                        </span>
                    </div>
                    <?php if (Gdn::session()->UserID && Gdn::session()->UserID != $followUserID): ?>
                    <button class="bits-btn bits-follow-btn <?php echo $isFollowing ? 'bits-btn-following' : 'bits-btn-follow'; ?>"
                            data-userid="<?php echo $followUserID; ?>"
                            data-following="<?php echo $isFollowing ? '1' : '0'; ?>">
                        <?php echo $isFollowing ? t('Following', '已关注') : t('Follow', '关注'); ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="bits-empty-message">
                <?php
                if ($tab === 'following') {
                    echo t('No following yet', '暂无关注');
                } else {
                    echo t('No followers yet', '暂无粉丝');
                }
                ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bits-space-pagination">
            <?php if ($currentPage > 1): ?>
            <a href="<?php echo url($baseUrl . '/' . $tab . '?page=' . ($currentPage - 1)); ?>" class="bits-page-btn">
                <svg class="iconpark-icon"><use href="#left"></use></svg>
            </a>
            <?php endif; ?>

            <span class="bits-page-info"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

            <?php if ($currentPage < $totalPages): ?>
            <a href="<?php echo url($baseUrl . '/' . $tab . '?page=' . ($currentPage + 1)); ?>" class="bits-page-btn">
                <svg class="iconpark-icon"><use href="#right"></use></svg>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
