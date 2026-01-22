<?php
/**
 * BitsMesh Theme Settings View
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

echo heading(t('BitsMesh 主题设置'), '', '', [], '/dashboard/settings/bitsmesh');
?>

<div class="padded">
    <?php echo $this->Form->open(); ?>
    <?php echo $this->Form->errors(); ?>

    <section>
        <h2 class="subheading"><?php echo t('签到奖励设置'); ?></h2>
        <div class="padded-top">
            <p class="info"><?php echo t('配置每日签到时鸡腿奖励的二项分布随机算法参数。'); ?></p>
            <p class="info"><?php echo t('二项分布 B(n, p) 的期望值 = n × p，表示用户平均每次签到获得的鸡腿数。'); ?></p>
        </div>

        <ul>
            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('最大鸡腿数 (n)', 'CheckIn_DistributionN'); ?>
                    <div class="info"><?php echo t('二项分布的试验次数，决定理论最大值。范围：10-200'); ?></div>
                </div>
                <div class="input-wrap">
                    <?php echo $this->Form->textBox('CheckIn_DistributionN', [
                        'type' => 'number',
                        'min' => 10,
                        'max' => 200,
                        'step' => 1
                    ]); ?>
                </div>
            </li>

            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('成功概率 (p)', 'CheckIn_DistributionP'); ?>
                    <div class="info"><?php echo t('每次试验成功的概率，影响期望值。范围：0.01-0.5'); ?></div>
                </div>
                <div class="input-wrap">
                    <?php echo $this->Form->textBox('CheckIn_DistributionP', [
                        'type' => 'number',
                        'min' => 0.01,
                        'max' => 0.5,
                        'step' => 0.01
                    ]); ?>
                </div>
            </li>

            <li class="form-group">
                <div class="label-wrap">
                    <?php echo $this->Form->label('保底最小值', 'CheckIn_MinAmount'); ?>
                    <div class="info"><?php echo t('无论运气多差，签到至少获得的鸡腿数。范围：1-10'); ?></div>
                </div>
                <div class="input-wrap">
                    <?php echo $this->Form->textBox('CheckIn_MinAmount', [
                        'type' => 'number',
                        'min' => 1,
                        'max' => 10,
                        'step' => 1
                    ]); ?>
                </div>
            </li>
        </ul>

        <?php
        // 计算并显示分布预览
        $n = $this->data('CheckIn_DistributionN', 50);
        $p = $this->data('CheckIn_DistributionP', 0.1);
        $min = $this->data('CheckIn_MinAmount', 1);
        $expectedValue = round($n * $p, 1);

        // 计算标准差
        $stdDev = round(sqrt($n * $p * (1 - $p)), 1);

        // 约 95% 的用户落在 [E-2σ, E+2σ] 范围内
        $range95Low = max($min, round($expectedValue - 2 * $stdDev));
        $range95High = round($expectedValue + 2 * $stdDev);
        ?>
        <div class="padded alert alert-info">
            <strong><?php echo t('📊 分布预览'); ?></strong>
            <ul style="margin: 10px 0 0 20px; list-style: disc;">
                <li><?php echo sprintf(t('期望值（平均）：约 %s 鸡腿'), '<strong>' . $expectedValue . '</strong>'); ?></li>
                <li><?php echo sprintf(t('约 95%% 的用户获得：%d ~ %d 鸡腿'), $range95Low, $range95High); ?></li>
                <li><?php echo sprintf(t('理论最大值：%d 鸡腿（概率极低）'), $n); ?></li>
                <li><?php echo sprintf(t('保底最小值：%d 鸡腿'), $min); ?></li>
            </ul>
        </div>
    </section>

    <?php echo $this->Form->close('保存设置'); ?>
</div>
