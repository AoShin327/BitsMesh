<?php if (!defined('APPLICATION')) exit(); ?>

<h1><?php echo t('Nice Avatar Settings'); ?></h1>

<div class="padded">
    <div class="Info">
        <?php echo t('This plugin automatically generates unique avatars for new users during registration. Avatars are created client-side and saved as static images - no ongoing server processing required.'); ?>
    </div>

    <?php if (!$this->data('JsBundleExists')): ?>
    <div class="Warning">
        <strong><?php echo t('Warning'); ?>:</strong>
        <?php echo t('JavaScript bundle not found. Please run the build command:'); ?>
        <code>cd plugins/NiceAvatar/node && npm install && npm run build</code>
    </div>
    <?php else: ?>
    <div class="Info" style="background: #d4edda; border-color: #c3e6cb;">
        ✓ <?php echo t('JavaScript bundle is ready. Avatars will be generated on registration.'); ?>
    </div>
    <?php endif; ?>
</div>

<?php
echo $this->Form->open();
echo $this->Form->errors();
?>

<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Enable Nice Avatar', 'Plugins_NiceAvatar_Enabled'); ?>
            <div class="info"><?php echo t('When enabled, new users will automatically receive a generated avatar during registration.'); ?></div>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->checkBox('Plugins_NiceAvatar_Enabled', '', ['value' => true, 'default' => true]); ?>
        </div>
    </li>
</ul>

<?php echo $this->Form->close('Save'); ?>

<h2><?php echo t('Avatar Preview'); ?></h2>

<div class="padded">
    <p><?php echo t('Preview of avatar styles with different email seeds:'); ?></p>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 16px 0;">
        <div data-nice-avatar="user1@example.com" data-size="64" style="width: 64px; height: 64px;"></div>
        <div data-nice-avatar="user2@example.com" data-size="64" style="width: 64px; height: 64px;"></div>
        <div data-nice-avatar="user3@example.com" data-size="64" style="width: 64px; height: 64px;"></div>
        <div data-nice-avatar="admin@forum.com" data-size="64" style="width: 64px; height: 64px;"></div>
        <div data-nice-avatar="test@test.com" data-size="64" style="width: 64px; height: 64px;"></div>
    </div>
</div>

<h2><?php echo t('How It Works'); ?></h2>

<div class="padded">
    <ol style="line-height: 1.8;">
        <li><?php echo t('User enters their email on the registration form'); ?></li>
        <li><?php echo t('A unique avatar is generated based on the email address'); ?></li>
        <li><?php echo t('Avatar is converted to PNG and saved as a static image'); ?></li>
        <li><?php echo t('No additional database queries or server processing needed'); ?></li>
    </ol>
</div>
