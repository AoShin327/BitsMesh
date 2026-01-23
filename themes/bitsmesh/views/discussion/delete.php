<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh Theme - Custom Single Discussion Delete Confirmation Dialog
 *
 * Adds moderation log options: IsPublic, Reason, CreditsChange
 * This view overrides applications/vanilla/views/discussion/delete.php
 */
?>
<h1><?php echo $this->data('Title'); ?></h1>

<?php
echo $this->Form->open();
echo $this->Form->errors();

echo '<div class="P">'.sprintf(t('Are you sure you want to delete this %s?'), t('discussion')).'</div>';
?>

<ul class="bits-moderation-options">
    <li class="form-group">
        <?php echo $this->Form->checkBox('IsPublic', t('PublishToModerationLog', 'Publish to moderation log'), ['value' => '1', 'checked' => true]); ?>
    </li>
    <li class="form-group">
        <?php echo $this->Form->label(t('ModerationReason', 'Reason'), 'Reason'); ?>
        <?php echo $this->Form->textBox('Reason', ['class' => 'InputBox', 'placeholder' => t('ModerationReasonPlaceholder', 'Enter the reason for this action...')]); ?>
    </li>
    <li class="form-group bits-credits-group">
        <?php echo $this->Form->label(t('CreditsChange', 'Credits change'), 'CreditsChange'); ?>
        <div class="bits-credits-input">
            <?php echo $this->Form->dropDown('CreditsAction', [
                '' => t('NoChange', 'No change'),
                'add' => t('AddCredits', 'Add credits'),
                'subtract' => t('SubtractCredits', 'Subtract credits')
            ], ['class' => 'InputBox']); ?>
            <?php echo $this->Form->textBox('CreditsAmount', ['class' => 'InputBox', 'type' => 'number', 'min' => '0', 'placeholder' => '0', 'style' => 'width: 80px; margin-left: 8px;']); ?>
            <span class="bits-credits-label"><?php echo t('ChickenLegs', 'credits'); ?></span>
        </div>
        <div class="bits-credits-reason" style="margin-top: 8px;">
            <?php echo $this->Form->textBox('CreditsReason', ['class' => 'InputBox', 'placeholder' => t('CreditsReasonPlaceholder', 'Reason for credits change (optional)...')]); ?>
        </div>
    </li>
</ul>

<div class="Buttons Buttons-Confirm">
    <?php
    echo $this->Form->button('OK', ['class' => 'Button Primary']);
    echo $this->Form->button('Cancel', ['type' => 'button', 'class' => 'Button Close']);
    ?>
</div>

<?php echo $this->Form->close(); ?>

<style>
.bits-moderation-options {
    list-style: none;
    padding: 0;
    margin: 16px 0;
}
.bits-moderation-options .form-group {
    margin-bottom: 12px;
}
.bits-moderation-options .InputBox {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--bits-border, #ddd);
    border-radius: 4px;
    font-size: 14px;
}
.bits-moderation-options select.InputBox {
    width: 100%;
}
.bits-credits-input {
    display: flex;
    align-items: center;
    gap: 8px;
}
.bits-credits-input .InputBox {
    width: auto;
}
.bits-credits-input select.InputBox {
    width: auto;
    min-width: 120px;
}
.bits-credits-label {
    color: var(--bits-text-secondary, #666);
    font-size: 14px;
}
.bits-credits-reason .InputBox {
    width: 100%;
}
</style>
