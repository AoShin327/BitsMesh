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
                           value="<?php echo htmlspecialchars($this->Form->getValue('Themes_BitsMesh_GridColor', '#d4d4d4')); ?>"
                           class="bits-color-picker"
                           onchange="document.getElementById('Form_Themes_BitsMesh_GridColor').value = this.value">
                    <?php echo $this->Form->textBox('Themes_BitsMesh_GridColor', [
                        'class' => 'bits-color-hex InputBox',
                        'maxlength' => 7,
                        'placeholder' => '#d4d4d4'
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
                           class="bits-color-picker"
                           onchange="document.getElementById('Form_Themes_BitsMesh_DarkGridColor').value = this.value">
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
                           class="bits-color-picker"
                           onchange="document.getElementById('Form_Themes_BitsMesh_GridBgColor').value = this.value">
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
                           class="bits-color-picker"
                           onchange="document.getElementById('Form_Themes_BitsMesh_DarkGridBgColor').value = this.value">
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
    </div>

    <?php echo $this->Form->close(); ?>
</div>

<script>
// Sync color picker with text input
document.querySelectorAll('.bits-color-hex').forEach(function(input) {
    input.addEventListener('input', function() {
        var pickerId = this.id.replace('Form_Themes_BitsMesh_', 'ColorPicker_').replace('Color', '');
        var picker = document.getElementById(pickerId);
        if (picker && /^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            picker.value = this.value;
        }
    });
});
</script>
