<?php defined('APPLICATION') or exit();
/**
 * BitsMesh Invite Page
 *
 * User invite code management page.
 * Allows users to generate and manage invite codes.
 *
 * @package BitsMesh
 * @since 1.0
 */

// Add required assets
$this->addCssFile('bits-invite.css', 'themes/bitsmesh');
$this->addJsFile('invite.js', 'themes/bitsmesh');

// Get data
$credits = $this->data('Credits', 0);
$creditCost = $this->data('CreditCost', 1000);
$defaultMaxUses = $this->data('DefaultMaxUses', 1);
$defaultExpiryDays = $this->data('DefaultExpiryDays', 30);
$inviteCodes = $this->data('InviteCodes', []);
$invitedUsers = $this->data('InvitedUsers', []);
$invitedCount = $this->data('InvitedCount', 0);
$canGenerate = $this->data('CanGenerate', false);

// Page title
$this->title(t('Invite Friends', '邀请好友'));
?>

<div class="invite-page">
    <!-- Header -->
    <div class="invite-header">
        <h1 class="invite-title">
            <svg class="iconpark-icon" width="28" height="28"><use href="#key"></use></svg>
            <?php echo t('Invite Friends', '邀请好友'); ?>
        </h1>
        <p class="invite-subtitle"><?php echo t('Generate invite codes and earn rewards when friends register', '生成邀请码，好友注册后可获得奖励'); ?></p>
    </div>

    <!-- Generate Section -->
    <div class="invite-section invite-card">
        <h2 class="section-title">
            <svg class="iconpark-icon" width="20" height="20"><use href="#add-one"></use></svg>
            <?php echo t('Generate Invite Code', '生成邀请码'); ?>
        </h2>

        <div class="generate-info">
            <div class="info-row">
                <span class="info-label"><?php echo t('Current Balance', '当前余额'); ?>:</span>
                <span class="info-value credits-value">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#chicken-leg"></use></svg>
                    <span id="current-credits"><?php echo number_format($credits); ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><?php echo t('Cost per Code', '每个邀请码消耗'); ?>:</span>
                <span class="info-value cost-value">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#chicken-leg"></use></svg>
                    <?php echo number_format($creditCost); ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><?php echo t('Code Validity', '邀请码有效期'); ?>:</span>
                <span class="info-value"><?php echo sprintf(t('%d days', '%d 天'), $defaultExpiryDays); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><?php echo t('Uses per Code', '每个邀请码可用次数'); ?>:</span>
                <span class="info-value"><?php echo $defaultMaxUses; ?></span>
            </div>
        </div>

        <div class="generate-action">
            <?php if ($canGenerate): ?>
            <button type="button" id="generate-code-btn" class="btn btn-primary btn-generate">
                <svg class="iconpark-icon" width="16" height="16"><use href="#add-one"></use></svg>
                <?php echo sprintf(t('Generate Code (%d chicken legs)', '生成邀请码 (%d 鸡腿)'), $creditCost); ?>
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-disabled btn-generate" disabled>
                <svg class="iconpark-icon" width="16" height="16"><use href="#attention"></use></svg>
                <?php echo t('Insufficient Credits', '鸡腿不足'); ?>
            </button>
            <p class="insufficient-hint">
                <?php echo sprintf(t('You need %d chicken legs to generate an invite code.', '生成邀请码需要 %d 个鸡腿。'), $creditCost); ?>
                <a href="/progress"><?php echo t('Learn how to earn', '了解如何获取'); ?></a>
            </p>
            <?php endif; ?>
        </div>

        <!-- New Code Display (hidden by default) -->
        <div id="new-code-display" class="new-code-display hidden">
            <div class="success-icon">
                <svg class="iconpark-icon" width="32" height="32"><use href="#check-one"></use></svg>
            </div>
            <p class="success-message"><?php echo t('Invite code generated successfully!', '邀请码生成成功！'); ?></p>
            <div class="code-container">
                <code id="new-code-value" class="code-value"></code>
                <button type="button" class="btn btn-copy" data-copy-target="new-code-value">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#copy"></use></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- My Invite Codes Section -->
    <div class="invite-section invite-card">
        <h2 class="section-title">
            <svg class="iconpark-icon" width="20" height="20"><use href="#list-view"></use></svg>
            <?php echo t('My Invite Codes', '我的邀请码'); ?>
            <span class="count-badge"><?php echo count($inviteCodes); ?></span>
        </h2>

        <?php if (empty($inviteCodes)): ?>
        <div class="empty-state">
            <svg class="iconpark-icon" width="48" height="48"><use href="#inbox"></use></svg>
            <p><?php echo t('You haven\'t generated any invite codes yet.', '你还没有生成任何邀请码'); ?></p>
        </div>
        <?php else: ?>
        <div class="codes-list">
            <?php foreach ($inviteCodes as $code):
                $isExpired = $code['ExpiresAt'] && strtotime($code['ExpiresAt']) < time();
                $isExhausted = $code['UseCount'] >= $code['MaxUses'];
                $isActive = $code['IsActive'] && !$isExpired && !$isExhausted;
                $statusClass = $isActive ? 'active' : ($isExpired ? 'expired' : ($isExhausted ? 'exhausted' : 'disabled'));
            ?>
            <div class="code-item <?php echo $statusClass; ?>">
                <div class="code-main">
                    <code class="code-text"><?php echo htmlspecialchars($code['Code']); ?></code>
                    <button type="button" class="btn-icon btn-copy-small" data-copy-text="<?php echo htmlspecialchars($code['Code']); ?>" title="<?php echo t('Copy', '复制'); ?>">
                        <svg class="iconpark-icon" width="14" height="14"><use href="#copy"></use></svg>
                    </button>
                </div>
                <div class="code-meta">
                    <span class="meta-item">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#peoples"></use></svg>
                        <?php echo $code['UseCount']; ?>/<?php echo $code['MaxUses']; ?>
                    </span>
                    <span class="meta-item">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#time"></use></svg>
                        <?php
                        if ($code['ExpiresAt']) {
                            echo Gdn_Format::date($code['ExpiresAt'], 'Y-m-d');
                        } else {
                            echo t('Never', '永不过期');
                        }
                        ?>
                    </span>
                    <span class="status-badge status-<?php echo $statusClass; ?>">
                        <?php
                        if (!$code['IsActive']) {
                            echo t('Disabled', '已禁用');
                        } elseif ($isExpired) {
                            echo t('Expired', '已过期');
                        } elseif ($isExhausted) {
                            echo t('Used Up', '已用完');
                        } else {
                            echo t('Available', '可用');
                        }
                        ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Invited Users Section -->
    <div class="invite-section invite-card">
        <h2 class="section-title">
            <svg class="iconpark-icon" width="20" height="20"><use href="#peoples"></use></svg>
            <?php echo t('Users I Invited', '我邀请的用户'); ?>
            <span class="count-badge"><?php echo $invitedCount; ?></span>
        </h2>

        <?php if (empty($invitedUsers)): ?>
        <div class="empty-state">
            <svg class="iconpark-icon" width="48" height="48"><use href="#peoples"></use></svg>
            <p><?php echo t('No one has registered with your invite code yet.', '还没有人使用你的邀请码注册'); ?></p>
        </div>
        <?php else: ?>
        <div class="invited-users-list">
            <?php foreach ($invitedUsers as $user):
                $photo = val('Photo', $user, '');
                if ($photo && !isUrl($photo)) {
                    $photo = Gdn_Upload::url(changeBasename($photo, 'n%s'));
                } elseif (!$photo) {
                    $photo = UserModel::getDefaultAvatarUrl($user);
                }
            ?>
            <div class="invited-user-item">
                <a href="<?php echo url('/profile/' . val('UserID', $user)); ?>" class="user-avatar">
                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars(val('Name', $user)); ?>">
                </a>
                <div class="user-info">
                    <a href="<?php echo url('/profile/' . val('UserID', $user)); ?>" class="user-name">
                        <?php echo htmlspecialchars(val('Name', $user)); ?>
                    </a>
                    <span class="user-meta">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#time"></use></svg>
                        <?php echo Gdn_Format::date(val('DateInserted', $user), 'Y-m-d'); ?>
                    </span>
                </div>
                <span class="invite-code-used">
                    <?php echo htmlspecialchars(val('Code', $user, '')); ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Hidden data for JS -->
<script>
    window.InviteConfig = {
        generateUrl: '<?php echo url('/invite/generate'); ?>',
        transientKey: '<?php echo Gdn::session()->transientKey(); ?>',
        creditCost: <?php echo $creditCost; ?>
    };
</script>
