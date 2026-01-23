<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh User Setting View
 *
 * Modern forum style user settings page with tab navigation.
 * Implements profile info editing with avatar cropping.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

$user = $this->data('User');
$userID = $this->data('UserID');
$tab = $this->data('Tab', 'introduction');
$tabs = $this->data('Tabs', []);

// Get user photo
$photoUrl = userPhotoUrl($user);

// Get user profile fields
$bio = val('Bio', $user, '');
$signature = val('Signature', $user, '');
$readme = val('Readme', $user, '');

// Base URL for tabs
$baseUrl = '/setting';

// TransientKey for CSRF
$transientKey = Gdn::session()->transientKey();
?>

<div class="bits-setting-page">
    <!-- Page Header -->
    <div class="bits-setting-header">
        <h1 class="bits-setting-title">
            <svg class="iconpark-icon"><use href="#setting-two"></use></svg>
            <?php echo t('Settings', '设置'); ?>
        </h1>
    </div>

    <div class="bits-setting-container">
        <!-- Left Sidebar: Tab Navigation -->
        <div class="bits-setting-selector">
            <?php foreach ($tabs as $tabKey => $tabInfo): ?>
            <a href="<?php echo url($baseUrl . '#' . $tabKey); ?>"
               class="bits-setting-tab <?php echo ($tab === $tabKey) ? 'active' : ''; ?>"
               data-tab="<?php echo $tabKey; ?>">
                <svg class="iconpark-icon"><use href="#<?php echo $tabInfo['icon']; ?>"></use></svg>
                <span><?php echo $tabInfo['label']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Right Content Area -->
        <div class="bits-setting-content">
            <?php if ($tab === 'introduction'): ?>
            <!-- Profile Info Tab -->
            <div class="bits-setting-section" id="introduction">
                <h2 class="bits-section-title"><?php echo t('Profile Info', '个人信息'); ?></h2>

                <form id="bits-profile-form" class="bits-setting-form">
                    <input type="hidden" name="TransientKey" value="<?php echo $transientKey; ?>">

                    <!-- Avatar Section -->
                    <div class="bits-form-group bits-avatar-section">
                        <label class="bits-form-label"><?php echo t('Avatar', '头像'); ?></label>
                        <div class="bits-avatar-upload" id="bits-avatar-upload">
                            <img src="<?php echo htmlspecialchars($photoUrl); ?>"
                                 alt="<?php echo t('Avatar', '头像'); ?>"
                                 class="bits-avatar-preview"
                                 id="bits-avatar-preview">
                            <div class="bits-avatar-overlay">
                                <svg class="iconpark-icon"><use href="#camera"></use></svg>
                                <span><?php echo t('Change Avatar', '更换头像'); ?></span>
                            </div>
                        </div>
                        <p class="bits-form-hint"><?php echo t('Supports JPG, PNG. Max 2MB.', '支持 JPG、PNG 格式，最大 2MB'); ?></p>
                    </div>

                    <!-- Bio Section -->
                    <div class="bits-form-group">
                        <label class="bits-form-label" for="bits-bio">Bio</label>
                        <input type="text"
                               id="bits-bio"
                               name="Bio"
                               class="bits-form-input"
                               maxlength="255"
                               placeholder="<?php echo t('Describe yourself in one sentence', '请用一句话介绍自己'); ?>"
                               value="<?php echo htmlspecialchars($bio); ?>">
                        <div class="bits-form-counter">
                            <span id="bits-bio-count"><?php echo mb_strlen($bio); ?></span>/255
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="bits-form-group">
                        <label class="bits-form-label" for="bits-signature"><?php echo t('Signature', '签名'); ?></label>
                        <textarea id="bits-signature"
                                  name="Signature"
                                  class="bits-form-textarea"
                                  rows="4"
                                  placeholder="<?php echo t('Displayed below your posts. Supports Markdown.', '帖子内容下显示；支持 Markdown'); ?>"><?php echo htmlspecialchars($signature); ?></textarea>
                    </div>

                    <!-- Readme Section -->
                    <div class="bits-form-group">
                        <label class="bits-form-label" for="bits-readme">Readme</label>
                        <textarea id="bits-readme"
                                  name="Readme"
                                  class="bits-form-textarea"
                                  rows="6"
                                  placeholder="<?php echo t('Displayed on your profile page. Supports Markdown.', '用户主页中显示；支持 Markdown'); ?>"><?php echo htmlspecialchars($readme); ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="bits-form-actions">
                        <button type="submit" class="bits-btn-primary" id="bits-save-profile">
                            <svg class="iconpark-icon"><use href="#check-one"></use></svg>
                            <span><?php echo t('Save Changes', '保存修改'); ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <?php elseif ($tab === 'security'): ?>
            <!-- Security Tab -->
            <div class="bits-setting-section" id="security">
                <h2 class="bits-section-title"><?php echo t('Security', '安全'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Security settings coming soon...', '安全设置即将上线...'); ?></p>
                </div>
            </div>

            <?php elseif ($tab === 'contact'): ?>
            <!-- Contact Tab -->
            <div class="bits-setting-section" id="contact">
                <h2 class="bits-section-title"><?php echo t('Contact', '联系方式'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Contact settings coming soon...', '联系方式设置即将上线...'); ?></p>
                </div>
            </div>

            <?php elseif ($tab === 'block'): ?>
            <!-- Blocked Users Tab -->
            <div class="bits-setting-section" id="block">
                <h2 class="bits-section-title"><?php echo t('Blocked Users', '屏蔽用户'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Block settings coming soon...', '屏蔽用户设置即将上线...'); ?></p>
                </div>
            </div>

            <?php elseif ($tab === 'preference'): ?>
            <!-- Preferences Tab -->
            <div class="bits-setting-section" id="preference">
                <h2 class="bits-section-title"><?php echo t('Preferences', '常用偏好'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Preference settings coming soon...', '偏好设置即将上线...'); ?></p>
                </div>
            </div>

            <?php elseif ($tab === 'homepage'): ?>
            <!-- Homepage Sections Tab -->
            <div class="bits-setting-section" id="homepage">
                <h2 class="bits-section-title"><?php echo t('Homepage Sections', '首页版块'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Homepage settings coming soon...', '首页版块设置即将上线...'); ?></p>
                </div>
            </div>

            <?php elseif ($tab === 'extend'): ?>
            <!-- Extensions Tab -->
            <div class="bits-setting-section" id="extend">
                <h2 class="bits-section-title"><?php echo t('Extensions', '论坛扩展'); ?></h2>
                <div class="bits-setting-placeholder">
                    <p><?php echo t('Extension settings coming soon...', '论坛扩展设置即将上线...'); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Avatar Cropper Modal -->
<div class="bits-modal" id="bits-avatar-modal">
    <div class="bits-modal-backdrop"></div>
    <div class="bits-modal-content">
        <div class="bits-modal-header">
            <h3><?php echo t('Change Avatar', '更换头像'); ?></h3>
            <button type="button" class="bits-modal-close" id="bits-modal-close">
                <svg class="iconpark-icon"><use href="#close-small"></use></svg>
            </button>
        </div>
        <div class="bits-modal-body">
            <div class="bits-cropper-container" id="bits-cropper-container">
                <img id="bits-cropper-image" src="" alt="Crop preview">
            </div>
            <div class="bits-cropper-controls">
                <button type="button" class="bits-btn-icon" id="bits-zoom-out" title="<?php echo t('Zoom Out', '缩小'); ?>">
                    <svg class="iconpark-icon"><use href="#zoom-out"></use></svg>
                </button>
                <input type="range" id="bits-zoom-slider" min="0.1" max="3" step="0.1" value="1">
                <button type="button" class="bits-btn-icon" id="bits-zoom-in" title="<?php echo t('Zoom In', '放大'); ?>">
                    <svg class="iconpark-icon"><use href="#zoom-in"></use></svg>
                </button>
                <button type="button" class="bits-btn-icon" id="bits-rotate-left" title="<?php echo t('Rotate Left', '左旋转'); ?>">
                    <svg class="iconpark-icon"><use href="#rotate"></use></svg>
                </button>
                <button type="button" class="bits-btn-icon" id="bits-rotate-right" title="<?php echo t('Rotate Right', '右旋转'); ?>">
                    <svg class="iconpark-icon"><use href="#rotate" style="transform: scaleX(-1);"></use></svg>
                </button>
            </div>
        </div>
        <div class="bits-modal-footer">
            <label class="bits-btn-secondary bits-file-label">
                <svg class="iconpark-icon"><use href="#folder-open"></use></svg>
                <span><?php echo t('Select Image', '选择图片'); ?></span>
                <input type="file" id="bits-avatar-input" accept="image/jpeg,image/png,image/gif" hidden>
            </label>
            <div class="bits-modal-actions">
                <button type="button" class="bits-btn-secondary" id="bits-cancel-crop">
                    <?php echo t('Cancel', '取消'); ?>
                </button>
                <button type="button" class="bits-btn-primary" id="bits-confirm-crop">
                    <svg class="iconpark-icon"><use href="#check-one"></use></svg>
                    <span><?php echo t('Confirm', '确定'); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="bits-toast" id="bits-toast">
    <span class="bits-toast-message"></span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab click without page reload (hash-based navigation)
    const tabs = document.querySelectorAll('.bits-setting-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            // Allow default navigation for now (full page reload)
            // Future: implement AJAX tab switching
        });
    });

    // Check hash on load and update active tab
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const activeTab = document.querySelector('.bits-setting-tab[data-tab="' + hash + '"]');
        if (activeTab) {
            document.querySelectorAll('.bits-setting-tab').forEach(t => t.classList.remove('active'));
            activeTab.classList.add('active');
        }
    }
});
</script>
