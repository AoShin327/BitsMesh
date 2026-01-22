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

// Get distribution parameters for display
$checkInN = (int)c('BitsMesh.CheckIn.DistributionN', 50);
$checkInP = (float)c('BitsMesh.CheckIn.DistributionP', 0.1);
$checkInMin = (int)c('BitsMesh.CheckIn.MinAmount', 1);
$checkInExpected = round($checkInN * $checkInP);

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
                        <div class="credits-checkin-options">
                            <div class="credits-checkin-option" data-type="fixed">
                                <div class="credits-option-icon">
                                    <svg class="iconpark-icon"><use href="#protect"></use></svg>
                                </div>
                                <div class="credits-option-info">
                                    <span class="credits-option-title"><?php echo t('Credits.FixedCheckIn', '稳定签到'); ?></span>
                                    <span class="credits-option-desc"><?php echo t('Credits.FixedCheckInDesc', '固定获得 5 鸡腿'); ?></span>
                                </div>
                                <button type="button" class="credits-btn credits-btn-secondary credits-btn-checkin" data-type="fixed">
                                    <span>+5</span>
                                </button>
                            </div>
                            <div class="credits-checkin-option" data-type="random">
                                <div class="credits-option-icon">
                                    <svg class="iconpark-icon"><use href="#game-three"></use></svg>
                                </div>
                                <div class="credits-option-info">
                                    <span class="credits-option-title"><?php echo t('Credits.RandomCheckIn', '随机签到'); ?></span>
                                    <span class="credits-option-desc"><?php echo sprintf(t('Credits.RandomCheckInDesc', '约 %d（保底 %d，最高 %d）'), $checkInExpected, $checkInMin, $checkInN); ?></span>
                                </div>
                                <button type="button" class="credits-btn credits-btn-primary credits-btn-checkin" data-type="random">
                                    <span>?</span>
                                </button>
                            </div>
                        </div>
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
                    <?php echo t('Credits.CheckInTipChoice', '稳定签到固定获得 5 鸡腿；随机签到使用二项分布算法，期望值约为 5 但有机会获得更多！'); ?>
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
    var maxAmount = <?php echo $checkInN; ?>;

    /**
     * Number rolling animation for check-in result.
     */
    function animateNumber($element, finalValue, duration) {
        duration = duration || 1500;
        var startTime = Date.now();
        var interval = 1000 / 60;

        function update() {
            var elapsed = Date.now() - startTime;
            var progress = Math.min(elapsed / duration, 1);

            if (progress < 1) {
                var randomNum = Math.floor(Math.random() * maxAmount) + 1;
                $element.text('+' + randomNum);
                setTimeout(update, interval);
            } else {
                $element.text('+' + finalValue);
                $element.addClass('final');
            }
        }

        $element.removeClass('final');
        update();
    }

    // Handle check-in button clicks
    $('.credits-btn-checkin').on('click', function() {
        var $btn = $(this);
        var checkInType = $btn.data('type'); // 'fixed' or 'random'
        var $options = $('.credits-checkin-options');
        var $result = $('#credits-checkin-result');
        var $amount = $result.find('.credits-result-amount');

        // Disable all buttons
        $('.credits-btn-checkin').prop('disabled', true).addClass('loading');
        $btn.find('span').text('...');

        // For random type, show animation
        if (checkInType === 'random') {
            $result.fadeIn();
            animateNumber($amount, 0, 2000);
        }

        $.ajax({
            url: checkInUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                TransientKey: gdn.definition('TransientKey'),
                CheckInType: checkInType
            },
            success: function(response) {
                if (response.Success) {
                    var showResult = function() {
                        // Show result
                        $amount.text('+' + response.Amount).addClass('final success');
                        $result.addClass('success').fadeIn();

                        // Replace options with done message
                        $options.replaceWith(
                            '<div class="credits-checkin-done">' +
                            '<svg class="iconpark-icon"><use href="#check"></use></svg>' +
                            '<span>' + response.Message + '</span>' +
                            '</div>'
                        );

                        // Mark today in calendar
                        $('.credits-calendar-day.today').addClass('checked')
                            .append('<span class="credits-day-check"><svg class="iconpark-icon"><use href="#check-small"></use></svg></span>');

                        // Update consecutive days
                        $('.credits-consecutive span').text('<?php echo t('Credits.ConsecutiveDaysPrefix', '已连续签到 '); ?>' + response.Consecutive + '<?php echo t('Credits.ConsecutiveDaysSuffix', ' 天'); ?>');
                    };

                    if (checkInType === 'random') {
                        // Wait for animation then show result
                        setTimeout(showResult, 1500);
                    } else {
                        // Fixed type - show immediately
                        showResult();
                    }
                } else {
                    $result.fadeOut();
                    gdn.informError(response.Message);
                    $('.credits-btn-checkin').prop('disabled', false).removeClass('loading');
                    $btn.find('span').text(checkInType === 'fixed' ? '+5' : '?');
                }
            },
            error: function() {
                $result.fadeOut();
                gdn.informError('<?php echo t('Credits.CheckInError', '签到失败，请稍后重试'); ?>');
                $('.credits-btn-checkin').prop('disabled', false).removeClass('loading');
                $btn.find('span').text(checkInType === 'fixed' ? '+5' : '?');
            }
        });
    });
});
</script>
