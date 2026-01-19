<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh Theme Settings Page
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */
?>

<h1><?php echo t('BitsMesh Theme Settings'); ?></h1>

<div class="padded">
    <?php echo $this->Form->open(); ?>
    <?php echo $this->Form->errors(); ?>

    <!-- Color Presets Section -->
    <section class="bits-settings-section">
        <h2 class="subheading"><?php echo t('Color Presets'); ?></h2>
        <p class="info"><?php echo t('Select a preset color scheme or customize your own below.'); ?></p>

        <div class="bits-preset-grid" id="ColorPresetGrid">
            <?php foreach ($this->data('ColorPresets') as $key => $preset): ?>
                <button type="button" class="bits-preset-card" data-preset="<?php echo htmlspecialchars($key); ?>"
                        data-primary="<?php echo htmlspecialchars($preset['primary']); ?>"
                        data-secondary="<?php echo htmlspecialchars($preset['secondary']); ?>"
                        data-dark-primary="<?php echo htmlspecialchars($preset['darkPrimary']); ?>"
                        data-dark-secondary="<?php echo htmlspecialchars($preset['darkSecondary']); ?>">
                    <span class="bits-preset-color" style="background: <?php echo htmlspecialchars($preset['primary']); ?>"></span>
                    <span class="bits-preset-name"><?php echo htmlspecialchars($preset['name']); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Light Mode Colors -->
    <section class="bits-settings-section">
        <h2 class="subheading"><?php echo t('Light Mode Colors'); ?></h2>

        <ul class="form-group-list">
            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Primary Color', 'Themes_BitsMesh_PrimaryColor'); ?>
                    <div class="info"><?php echo t('Main accent color for buttons and links.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_Primary"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_PrimaryColor', '#3B82F6')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_PrimaryColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#3B82F6'
                    ]); ?>
                </div>
            </li>

            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Secondary Color', 'Themes_BitsMesh_SecondaryColor'); ?>
                    <div class="info"><?php echo t('Hover and accent variations.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_Secondary"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_SecondaryColor', '#60A5FA')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_SecondaryColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#60A5FA'
                    ]); ?>
                </div>
            </li>
        </ul>
    </section>

    <!-- Dark Mode Colors -->
    <section class="bits-settings-section">
        <h2 class="subheading"><?php echo t('Dark Mode Colors'); ?></h2>

        <ul class="form-group-list">
            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Dark Primary Color', 'Themes_BitsMesh_DarkPrimaryColor'); ?>
                    <div class="info"><?php echo t('Primary color used in dark mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_DarkPrimary"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_DarkPrimaryColor', '#2563EB')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_DarkPrimaryColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#2563EB'
                    ]); ?>
                </div>
            </li>

            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Dark Secondary Color', 'Themes_BitsMesh_DarkSecondaryColor'); ?>
                    <div class="info"><?php echo t('Secondary color used in dark mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_DarkSecondary"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_DarkSecondaryColor', '#3B82F6')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_DarkSecondaryColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#3B82F6'
                    ]); ?>
                </div>
            </li>
        </ul>
    </section>

    <!-- Grid Background Settings -->
    <section class="bits-settings-section">
        <h2 class="subheading"><?php echo t('Grid Background'); ?></h2>

        <ul class="form-group-list">
            <li class="form-group">
                <div class="label-wrap-wide">
                    <?php echo $this->Form->toggle(
                        'Themes_BitsMesh_GridEnabled',
                        t('Enable Grid Background'),
                        [],
                        t('Shows a subtle grid pattern on large screens (>500px).')
                    ); ?>
                </div>
            </li>

            <li class="form-group" id="GridColorGroup">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Grid Color (Light Mode)', 'Themes_BitsMesh_GridColor'); ?>
                    <div class="info"><?php echo t('Grid line color for light mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_Grid"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_GridColor', '#e5e7eb')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_GridColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#e5e7eb'
                    ]); ?>
                </div>
            </li>

            <li class="form-group" id="DarkGridColorGroup">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Grid Color (Dark Mode)', 'Themes_BitsMesh_DarkGridColor'); ?>
                    <div class="info"><?php echo t('Grid line color for dark mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_DarkGrid"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_DarkGridColor', '#404040')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_DarkGridColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#404040'
                    ]); ?>
                </div>
            </li>

            <li class="form-group" id="GridBgColorGroup">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Background Color (Light Mode)', 'Themes_BitsMesh_GridBgColor'); ?>
                    <div class="info"><?php echo t('Page background color for light mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_GridBg"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_GridBgColor', '#fffcf8')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_GridBgColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#fffcf8'
                    ]); ?>
                </div>
            </li>

            <li class="form-group" id="DarkGridBgColorGroup">
                <div class="label-wrap">
                    <?php echo $this->Form->label('Background Color (Dark Mode)', 'Themes_BitsMesh_DarkGridBgColor'); ?>
                    <div class="info"><?php echo t('Page background color for dark mode.'); ?></div>
                </div>
                <div class="input-wrap bits-color-wrap">
                    <input type="color" id="ColorPicker_DarkGridBg"
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_DarkGridBgColor', '#1a1a1a')); ?>"
                           class="bits-color-picker">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_DarkGridBgColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#1a1a1a'
                    ]); ?>
                </div>
            </li>
        </ul>
    </section>

    <!-- Submit Button -->
    <div class="form-footer">
        <?php echo $this->Form->button('Save', ['class' => 'btn btn-primary']); ?>
        <button type="button" class="btn" id="ResetDefaultsBtn"><?php echo t('Reset to Defaults'); ?></button>
    </div>

    <?php echo $this->Form->close(); ?>
</div>

<script>
// Pass default colors to JavaScript
window.bitsDefaultColors = <?php echo json_encode($this->data('DefaultColors')); ?>;
</script>
