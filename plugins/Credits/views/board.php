<?php if (!defined('APPLICATION')) exit();
/**
 * Check-in Page View (Board)
 *
 * @author BitsMesh
 */

$credits = $this->data('Credits');
$level = $this->data('Level');
$canCheckIn = $this->data('CanCheckIn');
$consecutiveDays = $this->data('ConsecutiveDays');
$lastCheckIn = $this->data('LastCheckIn');
$checkedDays = $this->data('CheckedDays');
$year = $this->data('CalendarYear');
$month = $this->data('CalendarMonth');

// Calendar calculations
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$firstDayWeekday = date('w', $firstDayOfMonth); // 0 = Sunday
$monthName = date('Y年m月', $firstDayOfMonth);
$today = (int)date('j');
$currentMonth = date('Y-m');
$isCurrentMonth = ($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) === $currentMonth;
?>

<div class="credits-page credits-checkin-page">
    <div class="credits-container">

        <!-- Check-in Card -->
        <div class="credits-card credits-checkin-card">
            <div class="credits-card-header">
                <h2>
                    <svg class="iconpark-icon"><use href="#plan"></use></svg>
                    <?php echo t('Credits.DailyCheckIn', '每日签到'); ?>
                </h2>
            </div>
            <div class="credits-card-body">
                <div class="credits-checkin-status">
                    <div class="credits-consecutive">
                        <svg class="iconpark-icon"><use href="#fire"></use></svg>
                        <span><?php echo sprintf(t('Credits.ConsecutiveDays', '已连续签到 %d 天'), $consecutiveDays); ?></span>
                    </div>

                    <div class="credits-checkin-action">
                        <?php if ($canCheckIn): ?>
                        <button type="button" id="credits-checkin-btn" class="credits-btn credits-btn-primary credits-btn-checkin">
                            <svg class="iconpark-icon"><use href="#plan"></use></svg>
                            <span><?php echo t('Credits.DoCheckIn', '立即签到'); ?></span>
                        </button>
                        <?php else: ?>
                        <div class="credits-checkin-done">
                            <svg class="iconpark-icon"><use href="#check"></use></svg>
                            <span><?php echo t('Credits.AlreadyCheckedIn', '今日已签到'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="credits-checkin-result" id="credits-checkin-result" style="display: none;">
                        <div class="credits-result-icon">
                            <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
                        </div>
                        <div class="credits-result-text">
                            <span class="credits-result-amount">+0</span>
                            <span class="credits-result-label"><?php echo t('Credits.ChickenLegs', '鸡腿'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="credits-checkin-tip">
                    <svg class="iconpark-icon"><use href="#tips"></use></svg>
                    <?php echo sprintf(t('Credits.CheckInTip', '每日签到可随机获得 %d~%d 鸡腿'), CreditsPlugin::CREDIT_CHECKIN_MIN, CreditsPlugin::CREDIT_CHECKIN_MAX); ?>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="credits-card credits-calendar-card">
            <div class="credits-card-header">
                <h2>
                    <svg class="iconpark-icon"><use href="#calendar-thirty"></use></svg>
                    <?php echo t('Credits.CheckInCalendar', '签到日历'); ?>
                </h2>
                <span class="credits-calendar-month"><?php echo $monthName; ?></span>
            </div>
            <div class="credits-card-body">
                <div class="credits-calendar">
                    <div class="credits-calendar-header">
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Sun', '日'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Mon', '一'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Tue', '二'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Wed', '三'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Thu', '四'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Fri', '五'); ?></div>
                        <div class="credits-calendar-day-name"><?php echo t('Credits.Sat', '六'); ?></div>
                    </div>
                    <div class="credits-calendar-body">
                        <?php
                        // Empty cells for days before the first of month
                        for ($i = 0; $i < $firstDayWeekday; $i++) {
                            echo '<div class="credits-calendar-day empty"></div>';
                        }

                        // Days of the month
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $isChecked = isset($checkedDays[$day]);
                            $isToday = $isCurrentMonth && $day === $today;
                            $classes = ['credits-calendar-day'];
                            if ($isChecked) $classes[] = 'checked';
                            if ($isToday) $classes[] = 'today';
                            if (!$isCurrentMonth || $day < $today) $classes[] = 'past';

                            echo '<div class="' . implode(' ', $classes) . '">';
                            echo '<span class="credits-day-number">' . $day . '</span>';
                            if ($isChecked) {
                                echo '<span class="credits-day-check"><svg class="iconpark-icon"><use href="#check-small"></use></svg></span>';
                            }
                            echo '</div>';
                        }

                        // Empty cells for days after the end of month
                        $totalCells = $firstDayWeekday + $daysInMonth;
                        $remainingCells = (7 - ($totalCells % 7)) % 7;
                        for ($i = 0; $i < $remainingCells; $i++) {
                            echo '<div class="credits-calendar-day empty"></div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="credits-calendar-legend">
                    <div class="credits-legend-item">
                        <span class="credits-legend-dot checked"></span>
                        <span><?php echo t('Credits.Checked', '已签到'); ?></span>
                    </div>
                    <div class="credits-legend-item">
                        <span class="credits-legend-dot today"></span>
                        <span><?php echo t('Credits.Today', '今日'); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var checkInUrl = gdn.definition('Credits.CheckInUrl', '/credits/checkin');

    $('#credits-checkin-btn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: checkInUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                TransientKey: gdn.definition('TransientKey')
            },
            success: function(response) {
                if (response.Success) {
                    // Show result
                    $('#credits-checkin-result')
                        .find('.credits-result-amount').text('+' + response.Amount).end()
                        .fadeIn();

                    // Update button
                    $btn.replaceWith(
                        '<div class="credits-checkin-done">' +
                        '<svg class="iconpark-icon"><use href="#check"></use></svg>' +
                        '<span>' + response.Message + '</span>' +
                        '</div>'
                    );

                    // Mark today as checked in calendar
                    $('.credits-calendar-day.today').addClass('checked')
                        .append('<span class="credits-day-check"><svg class="iconpark-icon"><use href="#check-small"></use></svg></span>');

                    // Update consecutive days display
                    $('.credits-consecutive span').text('已连续签到 ' + response.Consecutive + ' 天');
                } else {
                    gdn.informError(response.Message);
                    $btn.prop('disabled', false).removeClass('loading');
                }
            },
            error: function() {
                gdn.informError('<?php echo t('Credits.CheckInError', '签到失败，请稍后重试'); ?>');
                $btn.prop('disabled', false).removeClass('loading');
            }
        });
    });
});
</script>
