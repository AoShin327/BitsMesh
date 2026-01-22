<?php if (!defined('APPLICATION')) exit();
/**
 * Progress Page - Level Progress View
 *
 * @author BitsMesh
 */

$credits = $this->data('Credits');
$level = $this->data('Level');
$progress = $this->data('Progress');
$thresholds = $this->data('LevelThresholds');
$rules = $this->data('CreditRules');
?>

<div class="credits-page credits-progress-page">
    <div class="credits-container">

        <!-- Current Status Card -->
        <div class="credits-card credits-status-card">
            <div class="credits-card-header">
                <h2><?php echo t('Credits.CurrentStatus', '当前状态'); ?></h2>
            </div>
            <div class="credits-card-body">
                <div class="credits-status-grid">
                    <div class="credits-status-item">
                        <div class="credits-status-label"><?php echo t('Credits.CurrentLevel', '当前等级'); ?></div>
                        <div class="credits-status-value credits-level">
                            <span class="level-badge level-<?php echo $level; ?>">Lv<?php echo $level; ?></span>
                        </div>
                    </div>
                    <div class="credits-status-item">
                        <div class="credits-status-label"><?php echo t('Credits.CurrentCredits', '当前鸡腿'); ?></div>
                        <div class="credits-status-value credits-amount">
                            <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
                            <?php echo number_format($credits); ?>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <?php if ($level < 6): ?>
                <div class="credits-progress-section">
                    <div class="credits-progress-info">
                        <span><?php echo sprintf(t('Credits.ProgressToNext', '距离 Lv%d 还需 %s 鸡腿'), $progress['nextLevel'], number_format($progress['needed'])); ?></span>
                        <span><?php echo $progress['percentage']; ?>%</span>
                    </div>
                    <div class="credits-progress-bar">
                        <div class="credits-progress-fill" style="width: <?php echo $progress['percentage']; ?>%;">
                            <span class="credits-progress-text"><?php echo $progress['percentage']; ?>%</span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="credits-progress-section">
                    <div class="credits-max-level">
                        <svg class="iconpark-icon"><use href="#crown-two"></use></svg>
                        <?php echo t('Credits.MaxLevel', '已达到最高等级！'); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Credit Rules Card -->
        <div class="credits-card credits-rules-card">
            <div class="credits-card-header">
                <h2>
                    <svg class="iconpark-icon"><use href="#book-one"></use></svg>
                    <?php echo t('Credits.EarnRules', '鸡腿获取规则'); ?>
                </h2>
            </div>
            <div class="credits-card-body">
                <table class="credits-rules-table">
                    <thead>
                        <tr>
                            <th><?php echo t('Credits.Action', '行为'); ?></th>
                            <th><?php echo t('Credits.Reward', '奖励'); ?></th>
                            <th><?php echo t('Credits.DailyLimit', '每日上限'); ?></th>
                            <th><?php echo t('Credits.Note', '备注'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $key => $rule): ?>
                        <tr>
                            <td>
                                <svg class="iconpark-icon"><use href="#<?php echo $key === 'post' ? 'write' : ($key === 'comment' ? 'comments' : ($key === 'checkin' ? 'plan' : 'chicken-leg')); ?>"></use></svg>
                                <?php echo $rule['name']; ?>
                            </td>
                            <td class="credits-amount-cell"><?php echo $rule['amount']; ?></td>
                            <td><?php echo $rule['limit'] !== null ? $rule['limit'] : '-'; ?></td>
                            <td class="credits-note-cell"><?php echo $rule['note']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Level Table Card -->
        <div class="credits-card credits-levels-card">
            <div class="credits-card-header">
                <h2>
                    <svg class="iconpark-icon"><use href="#level"></use></svg>
                    <?php echo t('Credits.LevelTable', '等级升级表'); ?>
                </h2>
            </div>
            <div class="credits-card-body">
                <div class="credits-levels-grid">
                    <?php
                    $prevThreshold = 0;
                    foreach ($thresholds as $lv => $threshold):
                        $isCurrentLevel = ($lv === $level);
                        $isCompleted = ($lv < $level);
                        $increment = $threshold - $prevThreshold;
                    ?>
                    <div class="credits-level-item <?php echo $isCurrentLevel ? 'current' : ($isCompleted ? 'completed' : ''); ?>">
                        <div class="credits-level-badge">
                            <span class="level-badge level-<?php echo $lv; ?>">Lv<?php echo $lv; ?></span>
                        </div>
                        <div class="credits-level-info">
                            <div class="credits-level-threshold">
                                <?php echo number_format($threshold); ?> <?php echo t('Credits.ChickenLegs', '鸡腿'); ?>
                            </div>
                            <?php if ($lv > 1): ?>
                            <div class="credits-level-increment">
                                (+<?php echo number_format($increment); ?>)
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isCurrentLevel): ?>
                        <div class="credits-level-current-badge"><?php echo t('Credits.CurrentLevelBadge', '当前'); ?></div>
                        <?php elseif ($isCompleted): ?>
                        <div class="credits-level-completed-badge">
                            <svg class="iconpark-icon"><use href="#check"></use></svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                        $prevThreshold = $threshold;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>
