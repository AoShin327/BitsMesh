<?php
/**
 * Friend Links Page View
 *
 * Displays friend link cards in a responsive grid layout.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

$links = $this->data('FriendLinks', []);
?>

<div class="bits-page-container">
    <div class="bits-page-header">
        <h1>
            <svg class="iconpark-icon" width="24" height="24"><use href="#earth"></use></svg>
            <?php echo t('FriendLinks', '友站链接'); ?>
        </h1>
        <p class="bits-page-description"><?php echo t('FriendLinks.Description', '优质站点推荐，拓展您的视野'); ?></p>
    </div>

    <?php if (empty($links)): ?>
    <div class="bits-empty-state">
        <svg class="iconpark-icon" width="48" height="48"><use href="#earth"></use></svg>
        <p><?php echo t('FriendLinks.Empty', '暂无友站链接'); ?></p>
    </div>
    <?php else: ?>
    <div class="bits-cards-grid">
        <?php foreach ($links as $link): ?>
        <div class="bits-card">
            <div class="bits-card-logo">
                <?php if (!empty($link['Logo'])): ?>
                <img src="<?php echo htmlspecialchars($link['Logo']); ?>" alt="<?php echo htmlspecialchars($link['Name']); ?>" />
                <?php else: ?>
                <svg class="iconpark-icon" width="48" height="48"><use href="#earth"></use></svg>
                <?php endif; ?>
            </div>
            <div class="bits-card-content">
                <h3 class="bits-card-title"><?php echo htmlspecialchars($link['Name']); ?></h3>
                <?php if (!empty($link['Description'])): ?>
                <p class="bits-card-description"><?php echo htmlspecialchars($link['Description']); ?></p>
                <?php endif; ?>
            </div>
            <div class="bits-card-footer">
                <a href="<?php echo htmlspecialchars($link['Url']); ?>" target="_blank" rel="noopener noreferrer" class="bits-card-button">
                    <svg class="iconpark-icon" width="14" height="14"><use href="#link-one"></use></svg>
                    <?php echo t('Visit', '访问'); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
