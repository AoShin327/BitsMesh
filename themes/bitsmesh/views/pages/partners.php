<?php
/**
 * Partners Page View
 *
 * Displays partner cards in a responsive grid layout.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

$partners = $this->data('Partners', []);
?>

<div class="bits-page-container">
    <div class="bits-page-header">
        <h1>
            <svg class="iconpark-icon" width="24" height="24"><use href="#shop"></use></svg>
            <?php echo t('Partners', '合作商家'); ?>
        </h1>
        <p class="bits-page-description"><?php echo t('Partners.Description', '我们的合作伙伴，为您提供优质服务'); ?></p>
    </div>

    <?php if (empty($partners)): ?>
    <div class="bits-empty-state">
        <svg class="iconpark-icon" width="48" height="48"><use href="#shop"></use></svg>
        <p><?php echo t('Partners.Empty', '暂无合作商家'); ?></p>
    </div>
    <?php else: ?>
    <div class="bits-cards-grid">
        <?php foreach ($partners as $partner): ?>
        <div class="bits-card">
            <div class="bits-card-logo">
                <?php if (!empty($partner['Logo'])): ?>
                <img src="<?php echo htmlspecialchars($partner['Logo']); ?>" alt="<?php echo htmlspecialchars($partner['Name']); ?>" />
                <?php else: ?>
                <svg class="iconpark-icon" width="48" height="48"><use href="#shop"></use></svg>
                <?php endif; ?>
            </div>
            <div class="bits-card-content">
                <h3 class="bits-card-title"><?php echo htmlspecialchars($partner['Name']); ?></h3>
                <?php if (!empty($partner['Description'])): ?>
                <p class="bits-card-description"><?php echo htmlspecialchars($partner['Description']); ?></p>
                <?php endif; ?>
            </div>
            <div class="bits-card-footer">
                <a href="<?php echo htmlspecialchars($partner['Url']); ?>" target="_blank" rel="noopener noreferrer" class="bits-card-button">
                    <svg class="iconpark-icon" width="14" height="14"><use href="#link-one"></use></svg>
                    <?php echo t('Visit', '访问'); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
