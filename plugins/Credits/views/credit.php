<?php if (!defined('APPLICATION')) exit();
/**
 * Credit History Page View
 *
 * @author BitsMesh
 */

$credits = $this->data('Credits');
$level = $this->data('Level');
$creditLog = $this->data('CreditLog');
$page = $this->data('Page');
$totalPages = $this->data('TotalPages');
$totalCount = $this->data('TotalCount');
?>

<div class="credits-page credits-history-page">
    <div class="credits-container">

        <!-- Header Card -->
        <div class="credits-card credits-header-card">
            <div class="credits-header-content">
                <div class="credits-header-title">
                    <h1>
                        <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
                        <?php echo t('Credits.CreditHistory', '鸡腿账簿'); ?>
                    </h1>
                </div>
                <div class="credits-header-balance">
                    <span class="credits-balance-label"><?php echo t('Credits.CurrentBalance', '当前余额'); ?></span>
                    <span class="credits-balance-value">
                        <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
                        <?php echo number_format($credits); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Transaction Log Card -->
        <div class="credits-card credits-log-card">
            <div class="credits-card-header">
                <h2><?php echo t('Credits.TransactionHistory', '交易记录'); ?></h2>
                <span class="credits-total-count"><?php echo sprintf(t('Credits.TotalRecords', '共 %d 条记录'), $totalCount); ?></span>
            </div>
            <div class="credits-card-body">
                <?php if (empty($creditLog)): ?>
                <div class="credits-empty">
                    <svg class="iconpark-icon"><use href="#inbox"></use></svg>
                    <p><?php echo t('Credits.NoRecords', '暂无交易记录'); ?></p>
                </div>
                <?php else: ?>
                <table class="credits-log-table">
                    <thead>
                        <tr>
                            <th><?php echo t('Credits.Time', '时间'); ?></th>
                            <th><?php echo t('Credits.Type', '类型'); ?></th>
                            <th><?php echo t('Credits.Change', '变动'); ?></th>
                            <th><?php echo t('Credits.Balance', '余额'); ?></th>
                            <th><?php echo t('Credits.Note', '备注'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($creditLog as $log): ?>
                        <tr>
                            <td class="credits-log-time">
                                <?php echo Gdn_Format::date($log['DateInserted'], 'html'); ?>
                            </td>
                            <td class="credits-log-type">
                                <?php echo CreditsPlugin::getTypeLabel($log['Type']); ?>
                            </td>
                            <td class="credits-log-amount <?php echo $log['Amount'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $log['Amount'] >= 0 ? '+' . number_format($log['Amount']) : number_format($log['Amount']); ?>
                            </td>
                            <td class="credits-log-balance">
                                <?php echo number_format($log['Balance']); ?>
                            </td>
                            <td class="credits-log-note">
                                <?php echo htmlspecialchars($log['Note'] ?: '-'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="credits-pagination">
                    <?php if ($page > 1): ?>
                    <a href="<?php echo url('/credit#/p-' . ($page - 1)); ?>" class="credits-page-link credits-page-prev">
                        <svg class="iconpark-icon"><use href="#left"></use></svg>
                        <?php echo t('Credits.PrevPage', '上一页'); ?>
                    </a>
                    <?php endif; ?>

                    <span class="credits-page-info">
                        <?php echo sprintf(t('Credits.PageInfo', '第 %d 页 / 共 %d 页'), $page, $totalPages); ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?php echo url('/credit#/p-' . ($page + 1)); ?>" class="credits-page-link credits-page-next">
                        <?php echo t('Credits.NextPage', '下一页'); ?>
                        <svg class="iconpark-icon"><use href="#right"></use></svg>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
