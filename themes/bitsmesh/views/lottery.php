<?php defined('APPLICATION') or exit();
/**
 * BitsMesh Lucky Draw Page
 *
 * A fair lottery tool based on Cloudflare drand random beacon.
 * Pure frontend implementation with verifiable results.
 *
 * @package BitsMesh
 * @since 1.0
 */

// Add required assets
$this->addCssFile('bits-lottery.css', 'themes/bitsmesh');
$this->addJsFile('lottery.js', 'themes/bitsmesh');

// Page title
$this->title(t('Lucky Draw', '幸运抽奖'));
?>

<div class="lottery-page">
    <!-- Header -->
    <div class="lottery-header">
        <h1 class="lottery-title">
            <svg class="iconpark-icon" width="28" height="28"><use href="#gift"></use></svg>
            <?php echo t('Lucky Draw', '幸运抽奖'); ?>
        </h1>
        <p class="lottery-subtitle"><?php echo t('Fair and verifiable lottery based on drand random beacon', '基于 drand 随机信标的公平可验证抽奖'); ?></p>
    </div>

    <!-- Configuration Form Section -->
    <div id="lottery-config" class="lottery-section lottery-card">
        <h2 class="section-title">
            <svg class="iconpark-icon" width="20" height="20"><use href="#setting-two"></use></svg>
            <?php echo t('Create Lottery', '创建抽奖'); ?>
        </h2>

        <form id="lottery-form" class="lottery-form">
            <!-- Post URL -->
            <div class="form-group">
                <label for="post-url"><?php echo t('Post URL', '帖子链接'); ?> <span class="required">*</span></label>
                <input type="text" id="post-url" name="postUrl" placeholder="https://example.com/post-123" required>
                <span class="form-hint"><?php echo t('Enter the discussion post URL', '输入讨论帖子的链接'); ?></span>
            </div>

            <!-- Draw Time -->
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="draw-date"><?php echo t('Draw Date', '开奖日期'); ?> <span class="required">*</span></label>
                    <input type="date" id="draw-date" name="drawDate" required>
                </div>
                <div class="form-group form-group-half">
                    <label for="draw-time"><?php echo t('Draw Time', '开奖时间'); ?> <span class="required">*</span></label>
                    <input type="time" id="draw-time" name="drawTime" required>
                </div>
            </div>

            <!-- Winner Count & Start Floor -->
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="winner-count"><?php echo t('Winner Count', '中奖人数'); ?> <span class="required">*</span></label>
                    <input type="number" id="winner-count" name="winnerCount" min="1" max="100" value="3" required>
                </div>
                <div class="form-group form-group-half">
                    <label for="start-floor"><?php echo t('Start Floor', '起始楼层'); ?></label>
                    <input type="number" id="start-floor" name="startFloor" min="1" value="1">
                    <span class="form-hint"><?php echo t('Default is 1 (first comment)', '默认为 1（第一条评论）'); ?></span>
                </div>
            </div>

            <!-- Unique Users Option -->
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" id="unique-users" name="uniqueUsers" checked>
                    <span><?php echo t('Exclude duplicate comments from same user', '排除同一用户的重复评论'); ?></span>
                </label>
            </div>

            <!-- Generate Button -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-generate">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#link-one"></use></svg>
                    <?php echo t('Generate Lottery Link', '生成抽奖链接'); ?>
                </button>
            </div>
        </form>

        <!-- Generated Link Display -->
        <div id="generated-link" class="generated-link hidden">
            <label><?php echo t('Generated Link', '生成的链接'); ?>:</label>
            <div class="link-container">
                <input type="text" id="lottery-link" readonly>
                <button type="button" class="btn btn-copy" data-copy-target="lottery-link">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#copy"></use></svg>
                </button>
            </div>
            <div class="link-actions">
                <a href="#" id="open-lottery-link" class="btn btn-secondary" target="_blank">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#preview-open"></use></svg>
                    <?php echo t('Open Link', '打开链接'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="lottery-loading" class="lottery-section lottery-card hidden">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php echo t('Loading lottery data...', '正在加载抽奖数据...'); ?></p>
        </div>
    </div>

    <!-- Error State -->
    <div id="lottery-error" class="lottery-section lottery-card hidden">
        <div class="error-message">
            <svg class="iconpark-icon" width="48" height="48"><use href="#attention"></use></svg>
            <h3><?php echo t('Error', '错误'); ?></h3>
            <p id="error-text"></p>
            <button type="button" class="btn btn-primary" id="retry-btn">
                <svg class="iconpark-icon" width="16" height="16"><use href="#refresh"></use></svg>
                <?php echo t('Retry', '重试'); ?>
            </button>
        </div>
    </div>

    <!-- Waiting State (Countdown) -->
    <div id="lottery-waiting" class="lottery-section lottery-card hidden">
        <div class="waiting-content">
            <h2 class="section-title">
                <svg class="iconpark-icon" width="20" height="20"><use href="#time"></use></svg>
                <?php echo t('Waiting for Draw', '等待开奖'); ?>
            </h2>

            <div class="lottery-info">
                <div class="info-item">
                    <span class="info-label"><?php echo t('Post', '帖子'); ?>:</span>
                    <span class="info-value" id="waiting-post-title"></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php echo t('Draw Time', '开奖时间'); ?>:</span>
                    <span class="info-value" id="waiting-draw-time"></span>
                </div>
            </div>

            <div class="countdown-container">
                <div class="countdown">
                    <div class="countdown-item">
                        <span class="countdown-value" id="countdown-days">00</span>
                        <span class="countdown-label"><?php echo t('Days', '天'); ?></span>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="countdown-hours">00</span>
                        <span class="countdown-label"><?php echo t('Hours', '时'); ?></span>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="countdown-minutes">00</span>
                        <span class="countdown-label"><?php echo t('Minutes', '分'); ?></span>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="countdown-seconds">00</span>
                        <span class="countdown-label"><?php echo t('Seconds', '秒'); ?></span>
                    </div>
                </div>
            </div>

            <div class="participation-stats">
                <div class="stat-item">
                    <span class="stat-value" id="valid-comments-count">0</span>
                    <span class="stat-label"><?php echo t('Valid Comments', '有效评论'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="unique-users-count">0</span>
                    <span class="stat-label"><?php echo t('Participants', '参与用户'); ?></span>
                </div>
            </div>

            <p class="waiting-hint">
                <svg class="iconpark-icon" width="16" height="16"><use href="#tips"></use></svg>
                <?php echo t('Refresh the page after draw time to see results', '开奖时间到达后刷新页面查看结果'); ?>
            </p>
        </div>
    </div>

    <!-- Results Section -->
    <div id="lottery-results" class="lottery-section lottery-card hidden">
        <h2 class="section-title">
            <svg class="iconpark-icon" width="20" height="20"><use href="#trophy"></use></svg>
            <?php echo t('Lottery Results', '抽奖结果'); ?>
        </h2>

        <!-- Lottery Info -->
        <div class="lottery-info results-info">
            <div class="info-item">
                <span class="info-label"><?php echo t('Post', '帖子'); ?>:</span>
                <a href="#" class="info-value info-link" id="result-post-title" target="_blank"></a>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo t('Draw Time', '开奖时间'); ?>:</span>
                <span class="info-value" id="result-draw-time"></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo t('Winner Count', '中奖人数'); ?>:</span>
                <span class="info-value" id="result-winner-count"></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo t('Valid Comments', '有效评论'); ?>:</span>
                <span class="info-value" id="result-valid-count"></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo t('Exclude Duplicates', '排除重复'); ?>:</span>
                <span class="info-value" id="result-unique"></span>
            </div>
        </div>

        <!-- Winners List -->
        <div class="winners-section">
            <h3 class="winners-title">
                <svg class="iconpark-icon" width="18" height="18"><use href="#crown"></use></svg>
                <?php echo t('Winners', '中奖名单'); ?>
            </h3>
            <ul id="winners-list" class="winners-list">
                <!-- Winners will be inserted here -->
            </ul>
        </div>

        <!-- @ Message Copy -->
        <div class="at-message-section">
            <label><?php echo t('Copy @ Message', '复制 @消息'); ?>:</label>
            <div class="at-message-container">
                <textarea id="at-message" readonly rows="2"></textarea>
                <button type="button" class="btn btn-copy" data-copy-target="at-message">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#copy"></use></svg>
                </button>
            </div>
        </div>

        <!-- Verification Info -->
        <details class="verification-section">
            <summary>
                <svg class="iconpark-icon" width="16" height="16"><use href="#check-one"></use></svg>
                <?php echo t('Verification Info', '验证信息'); ?>
            </summary>
            <div class="verification-content">
                <div class="verify-item">
                    <span class="verify-label"><?php echo t('Draw Timestamp', '开奖时间戳'); ?>:</span>
                    <code id="verify-timestamp"></code>
                </div>
                <div class="verify-item">
                    <span class="verify-label"><?php echo t('drand Round', 'drand 轮次'); ?>:</span>
                    <code id="verify-round"></code>
                </div>
                <div class="verify-item">
                    <span class="verify-label"><?php echo t('Randomness', '随机值'); ?>:</span>
                    <code id="verify-randomness" class="randomness-code"></code>
                </div>
                <div class="verify-item">
                    <span class="verify-label"><?php echo t('Random Source', '随机源'); ?>:</span>
                    <code id="verify-source"></code>
                </div>
                <p class="verify-hint">
                    <svg class="iconpark-icon" width="14" height="14"><use href="#info"></use></svg>
                    <?php echo t('Same parameters generate same results. Anyone can verify.', '相同参数生成相同结果，任何人可验证。'); ?>
                </p>
            </div>
        </details>
    </div>
</div>
