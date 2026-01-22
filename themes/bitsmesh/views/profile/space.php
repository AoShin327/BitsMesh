<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh User Space View
 *
 * Modern forum style user profile page with statistics cards,
 * tab navigation, and content lists.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

$user = $this->data('User');
$userID = $this->data('UserID');
$tab = $this->data('Tab', 'general');
$joinDays = $this->data('JoinDays', 0);
$level = $this->data('Level', 1);
$credits = $this->data('Credits', 0);
$discussionCount = $this->data('DiscussionCount', 0);
$commentCount = $this->data('CommentCount', 0);

// Get user photo - use userPhotoUrl() to ensure correct absolute URL
$photoUrl = userPhotoUrl($user);

// Base URL for tabs
$baseUrl = '/space/' . $userID;

// Tab definitions
$tabs = [
    'general' => ['label' => t('Overview', '概览'), 'icon' => 'home'],
    'thread' => ['label' => t('Topics', '主题帖'), 'icon' => 'edit'],
    'post' => ['label' => t('Replies', '回复'), 'icon' => 'comments-6ncdh3ka'],
    'favorite' => ['label' => t('Favorites', '收藏'), 'icon' => 'folder-focus'],
];
?>

<div class="bits-space-page">
    <!-- Head Container: Avatar + User Info + Actions -->
    <div class="bits-space-head">
        <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($user->Name); ?>" class="bits-space-avatar">
        <div class="bits-space-info">
            <h1 class="bits-space-username">
                <?php echo htmlspecialchars($user->Name); ?>
                <?php if ($level > 1): ?>
                <span class="bits-role-tag bits-level-<?php echo $level; ?>">Lv<?php echo $level; ?></span>
                <?php endif; ?>
            </h1>
            <p class="bits-space-bio"><?php echo t('Welcome to my space!', '一句话介绍自己'); ?></p>
        </div>
        <?php if (Gdn::session()->UserID && Gdn::session()->UserID != $userID): ?>
        <!-- Action buttons for viewing other users -->
        <div class="bits-space-actions">
            <a href="#" class="bits-btn bits-btn-follow">
                <?php echo t('Follow', '关注'); ?>
            </a>
            <a href="<?php echo url('/messages/add?to=' . urlencode($user->Name)); ?>" class="bits-btn bits-btn-message">
                <?php echo t('Message', '私信'); ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tab Selector -->
    <div class="bits-space-selector">
        <?php foreach ($tabs as $tabKey => $tabInfo): ?>
        <a href="<?php echo url($baseUrl . '/' . $tabKey); ?>" class="bits-select-item <?php echo ($tab === $tabKey) ? 'active' : ''; ?>">
            <svg class="iconpark-icon"><use href="#<?php echo $tabInfo['icon']; ?>"></use></svg>
            <span><?php echo $tabInfo['label']; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Statistics Cards -->
    <div class="bits-space-cards">
        <div class="bits-card-item">
            <svg class="iconpark-icon"><use href="#stopwatch-start"></use></svg>
            <div class="bits-card-name"><?php echo t('Join Days', '加入天数'); ?></div>
            <div class="bits-card-value"><?php echo number_format($joinDays); ?></div>
        </div>
        <div class="bits-card-item">
            <svg class="iconpark-icon"><use href="#level"></use></svg>
            <div class="bits-card-name"><?php echo t('Level', '等级'); ?></div>
            <div class="bits-card-value">Lv<?php echo $level; ?></div>
        </div>
        <div class="bits-card-item">
            <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
            <div class="bits-card-name"><?php echo t('Credits', '鸡腿数目'); ?></div>
            <div class="bits-card-value"><?php echo number_format($credits); ?></div>
        </div>
        <div class="bits-card-item">
            <svg class="iconpark-icon"><use href="#edit"></use></svg>
            <div class="bits-card-name"><?php echo t('Topics', '主题帖数'); ?></div>
            <div class="bits-card-value"><?php echo number_format($discussionCount); ?></div>
        </div>
        <div class="bits-card-item">
            <svg class="iconpark-icon"><use href="#comments-6ncdh3ka"></use></svg>
            <div class="bits-card-name"><?php echo t('Replies', '评论数目'); ?></div>
            <div class="bits-card-value"><?php echo number_format($commentCount); ?></div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="bits-space-content">
        <?php if ($tab === 'general'): ?>
        <!-- General Overview -->
        <div class="bits-space-section">
            <h3 class="bits-section-title">
                <svg class="iconpark-icon"><use href="#edit"></use></svg>
                <?php echo t('Recent Topics', '最近发布的主题'); ?>
            </h3>
            <?php
            $discussions = $this->data('RecentDiscussions');
            if ($discussions && $discussions->numRows() > 0):
            ?>
            <div class="bits-space-list">
                <?php foreach ($discussions as $discussion): ?>
                <a href="<?php echo discussionUrl($discussion); ?>" class="bits-space-list-item">
                    <span class="bits-list-title"><?php echo htmlspecialchars($discussion->Name); ?></span>
                    <span class="bits-list-meta"><?php echo Gdn_Format::date($discussion->DateInserted, 'html'); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if ($discussionCount > 5): ?>
            <a href="<?php echo url($baseUrl . '/thread'); ?>" class="bits-view-more"><?php echo t('View All', '查看全部'); ?> →</a>
            <?php endif; ?>
            <?php else: ?>
            <p class="bits-empty-message"><?php echo t('No topics yet', '暂无主题帖'); ?></p>
            <?php endif; ?>
        </div>

        <div class="bits-space-section">
            <h3 class="bits-section-title">
                <svg class="iconpark-icon"><use href="#comments-6ncdh3ka"></use></svg>
                <?php echo t('Recent Replies', '最近发布的回复'); ?>
            </h3>
            <?php
            $comments = $this->data('RecentComments');
            if ($comments && $comments->numRows() > 0):
            ?>
            <div class="bits-space-list">
                <?php foreach ($comments as $comment): ?>
                <a href="<?php echo commentUrl($comment); ?>" class="bits-space-list-item">
                    <span class="bits-list-title"><?php
                        // Strip HTML tags and decode entities for clean display
                        $bodyText = strip_tags(Gdn_Format::to($comment->Body, $comment->Format));
                        $bodyText = html_entity_decode($bodyText, ENT_QUOTES, 'UTF-8');
                        $bodyText = preg_replace('/\s+/', ' ', trim($bodyText)); // Normalize whitespace
                        echo htmlspecialchars(sliceString($bodyText, 80), ENT_QUOTES, 'UTF-8');
                    ?></span>
                    <span class="bits-list-meta"><?php echo Gdn_Format::date($comment->DateInserted, 'html'); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if ($commentCount > 5): ?>
            <a href="<?php echo url($baseUrl . '/post'); ?>" class="bits-view-more"><?php echo t('View All', '查看全部'); ?> →</a>
            <?php endif; ?>
            <?php else: ?>
            <p class="bits-empty-message"><?php echo t('No replies yet', '暂无评论'); ?></p>
            <?php endif; ?>
        </div>

        <?php elseif ($tab === 'thread'): ?>
        <!-- User's Topics -->
        <div class="bits-space-section">
            <h3 class="bits-section-title">
                <svg class="iconpark-icon"><use href="#edit"></use></svg>
                <?php echo sprintf(t('All Topics (%d)', '全部主题帖 (%d)'), $discussionCount); ?>
            </h3>
            <?php
            $discussions = $this->data('Discussions');
            if ($discussions && $discussions->numRows() > 0):
            ?>
            <div class="bits-space-list">
                <?php foreach ($discussions as $discussion): ?>
                <a href="<?php echo discussionUrl($discussion); ?>" class="bits-space-list-item">
                    <span class="bits-list-title"><?php echo htmlspecialchars($discussion->Name); ?></span>
                    <span class="bits-list-meta">
                        <span><?php echo t('Comments', '评论'); ?>: <?php echo $discussion->CountComments; ?></span>
                        <span><?php echo Gdn_Format::date($discussion->DateInserted, 'html'); ?></span>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="bits-empty-message"><?php echo t('No topics yet', '暂无主题帖'); ?></p>
            <?php endif; ?>
        </div>

        <?php elseif ($tab === 'post'): ?>
        <!-- User's Replies -->
        <div class="bits-space-section">
            <h3 class="bits-section-title">
                <svg class="iconpark-icon"><use href="#comments-6ncdh3ka"></use></svg>
                <?php echo sprintf(t('All Replies (%d)', '全部回复 (%d)'), $commentCount); ?>
            </h3>
            <?php
            $comments = $this->data('Comments');
            if ($comments && $comments->numRows() > 0):
            ?>
            <div class="bits-space-list">
                <?php foreach ($comments as $comment): ?>
                <a href="<?php echo commentUrl($comment); ?>" class="bits-space-list-item">
                    <div class="bits-list-content">
                        <span class="bits-list-excerpt"><?php
                            // Strip HTML tags and decode entities for clean display
                            $bodyText = strip_tags(Gdn_Format::to($comment->Body, $comment->Format));
                            $bodyText = html_entity_decode($bodyText, ENT_QUOTES, 'UTF-8');
                            $bodyText = preg_replace('/\s+/', ' ', trim($bodyText)); // Normalize whitespace
                            echo htmlspecialchars(sliceString($bodyText, 150), ENT_QUOTES, 'UTF-8');
                        ?></span>
                    </div>
                    <span class="bits-list-meta">
                        <span><?php echo Gdn_Format::date($comment->DateInserted, 'html'); ?></span>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="bits-empty-message"><?php echo t('No replies yet', '暂无评论'); ?></p>
            <?php endif; ?>
        </div>

        <?php elseif ($tab === 'favorite'): ?>
        <!-- User's Favorites -->
        <div class="bits-space-section">
            <h3 class="bits-section-title">
                <svg class="iconpark-icon"><use href="#folder-focus"></use></svg>
                <?php echo t('Favorites', '收藏夹'); ?>
            </h3>
            <?php
            $bookmarks = $this->data('Bookmarks');
            if (Gdn::session()->UserID != $userID): ?>
            <p class="bits-empty-message"><?php echo t('Private content', '仅自己可见'); ?></p>
            <?php elseif ($bookmarks && $bookmarks->numRows() > 0): ?>
            <div class="bits-space-list">
                <?php foreach ($bookmarks as $discussion): ?>
                <a href="<?php echo discussionUrl($discussion); ?>" class="bits-space-list-item">
                    <span class="bits-list-title"><?php echo htmlspecialchars($discussion->Name); ?></span>
                    <span class="bits-list-meta"><?php echo Gdn_Format::date($discussion->DateInserted, 'html'); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="bits-empty-message"><?php echo t('No favorites yet', '暂无收藏'); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($this->data('TotalPages', 0) > 1): ?>
        <div class="bits-space-pagination">
            <?php
            $currentPage = $this->data('CurrentPage', 1);
            $totalPages = $this->data('TotalPages', 1);

            if ($currentPage > 1): ?>
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
