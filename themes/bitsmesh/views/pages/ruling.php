<?php
/**
 * Moderation Log (管理记录) Page View
 *
 * Displays public moderation records in a table format with pagination.
 * Style inspired by modern forum designs.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

require_once PATH_THEMES . '/bitsmesh/models/class.moderationlogmodel.php';

$logs = $this->data('Logs', []);
$totalCount = $this->data('TotalCount', 0);
$pageCount = $this->data('PageCount', 1);
$currentPage = $this->data('CurrentPage', 1);
$perPage = $this->data('PerPage', 20);
?>

<div class="bits-ruling-container">
    <div class="bits-ruling-head">
        <h1 class="bits-ruling-title">
            <svg class="iconpark-icon" width="24" height="24"><use href="#balance-two"></use></svg>
            <?php echo t('ModerationLog', '管理记录'); ?>
        </h1>
        <div class="bits-ruling-stats">
            <?php echo t('Total Records', '共'); ?> <strong><?php echo number_format($totalCount); ?></strong> <?php echo t('Records', '条记录'); ?>
        </div>
    </div>

    <?php if (empty($logs)): ?>
    <div class="bits-empty-state bits-ruling-empty">
        <svg class="iconpark-icon" width="64" height="64"><use href="#balance-two"></use></svg>
        <p><?php echo t('ModerationLog.Empty', '暂无管理记录'); ?></p>
    </div>
    <?php else: ?>
    <div class="bits-ruling-table-wrapper">
        <table class="bits-ruling-table">
            <thead>
                <tr>
                    <th class="bits-col-id"><?php echo t('LogID', '编号'); ?></th>
                    <th class="bits-col-target"><?php echo t('Target', '对象'); ?></th>
                    <th class="bits-col-action"><?php echo t('Action', '操作说明'); ?></th>
                    <th class="bits-col-admin"><?php echo t('Admin', '管理员'); ?></th>
                    <th class="bits-col-time"><?php echo t('Time', '时间'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="bits-col-id">
                        <a href="/ruling#/id-<?php echo $log['ModerationLogID']; ?>" class="bits-log-id">
                            <?php echo $log['ModerationLogID']; ?>
                        </a>
                    </td>
                    <td class="bits-col-target">
                        <div class="bits-target-info">
                            <?php if (!empty($log['RecordUserName'])): ?>
                            <a href="/space/<?php echo $log['RecordUserID']; ?>" class="bits-target-user">
                                <?php echo htmlspecialchars($log['RecordUserName']); ?>
                            </a>
                            <?php echo t('of', '的'); ?>
                            <?php endif; ?>
                            <?php if (!empty($log['RecordUrl'])): ?>
                            <a href="<?php echo htmlspecialchars($log['RecordUrl']); ?>" class="bits-target-record">
                                <?php echo ModerationLogModel::formatRecordType($log['RecordType']); ?>
                            </a>
                            <?php else: ?>
                            <span class="bits-target-record">
                                <?php echo ModerationLogModel::formatRecordType($log['RecordType']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="bits-col-action">
                        <div class="bits-action-detail">
                            <?php echo nl2br(htmlspecialchars(ModerationLogModel::buildActionSummary($log))); ?>
                        </div>
                    </td>
                    <td class="bits-col-admin">
                        <a href="/space/<?php echo $log['InsertUserID']; ?>" class="bits-admin-link">
                            <?php echo htmlspecialchars($log['AdminName'] ?? t('Unknown', '未知')); ?>
                        </a>
                    </td>
                    <td class="bits-col-time">
                        <?php echo Gdn_Format::date($log['DateInserted'], '%Y/%m/%d %H:%M:%S'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pageCount > 1): ?>
    <div class="bits-ruling-pager">
        <?php
        // First page
        if ($currentPage > 1) {
            echo '<a href="/ruling/p1" class="pager-pos" title="' . t('First Page', '首页') . '">&laquo;</a> ';
        }

        // Previous page
        if ($currentPage > 1) {
            echo '<a href="/ruling/p' . ($currentPage - 1) . '" class="pager-pos">&lsaquo;</a> ';
        }

        // Page numbers
        $range = 2; // Show 2 pages before and after current
        $start = max(1, $currentPage - $range);
        $end = min($pageCount, $currentPage + $range);

        if ($start > 1) {
            echo '<a href="/ruling/p1" class="pager-pos">1</a> ';
            if ($start > 2) {
                echo '<span class="pager-pos">...</span> ';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                echo '<span class="pager-pos pager-cur">' . $i . '</span> ';
            } else {
                echo '<a href="/ruling/p' . $i . '" class="pager-pos">' . $i . '</a> ';
            }
        }

        if ($end < $pageCount) {
            if ($end < $pageCount - 1) {
                echo '<span class="pager-pos">...</span> ';
            }
            echo '<a href="/ruling/p' . $pageCount . '" class="pager-pos">' . $pageCount . '</a> ';
        }

        // Next page
        if ($currentPage < $pageCount) {
            echo '<a href="/ruling/p' . ($currentPage + 1) . '" class="pager-pos">&rsaquo;</a> ';
        }

        // Last page
        if ($currentPage < $pageCount) {
            echo '<a href="/ruling/p' . $pageCount . '" class="pager-pos" title="' . t('Last Page', '末页') . '">&raquo;</a>';
        }
        ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
