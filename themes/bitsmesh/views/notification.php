<?php
/**
 * BitsMesh Notification Center Page
 *
 * Displays user notifications with tabs:
 * - @Me (mentions)
 * - Reply (topic replies)
 * - Message (private messages)
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

// Get data from controller
$tab = $this->data('Tab', 'atme');
$mentions = $this->data('Mentions', []);
$replies = $this->data('Replies', []);
$conversations = $this->data('Conversations', []);
$unreadMentions = $this->data('UnreadMentions', 0);
$unreadReplies = $this->data('UnreadReplies', 0);
$unreadConversations = $this->data('UnreadConversations', 0);
$toUser = $this->data('ToUser', null);

// CSRF token for AJAX requests
$transientKey = Gdn::session()->transientKey();
?>

<div class="notification-page">
    <!-- Page Header -->
    <div class="notification-header">
        <h1 class="notification-title"><?php echo t('Notifications', '消息通知'); ?></h1>
        <button type="button" class="mark-all-read-btn" id="MarkAllReadBtn" data-transient-key="<?php echo htmlspecialchars($transientKey); ?>">
            <svg class="iconpark-icon" width="16" height="16"><use href="#check"></use></svg>
            <span><?php echo t('Mark All Read', '全部已读'); ?></span>
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="notification-tabs">
        <a href="#atMe" class="tab-item <?php echo $tab === 'atme' ? 'active' : ''; ?>" data-tab="atMe">
            <span class="tab-label"><?php echo t('Notification.AtMe', '@我'); ?></span>
            <?php if ($unreadMentions > 0): ?>
                <span class="badge"><?php echo $unreadMentions > 99 ? '99+' : $unreadMentions; ?></span>
            <?php endif; ?>
        </a>
        <a href="#reply" class="tab-item <?php echo $tab === 'reply' ? 'active' : ''; ?>" data-tab="reply">
            <span class="tab-label"><?php echo t('Notification.Replies', '回复主题'); ?></span>
            <?php if ($unreadReplies > 0): ?>
                <span class="badge"><?php echo $unreadReplies > 99 ? '99+' : $unreadReplies; ?></span>
            <?php endif; ?>
        </a>
        <a href="#message" class="tab-item <?php echo $tab === 'message' ? 'active' : ''; ?>" data-tab="message">
            <span class="tab-label"><?php echo t('Notification.Messages', '私信'); ?></span>
            <?php if ($unreadConversations > 0): ?>
                <span class="badge"><?php echo $unreadConversations > 99 ? '99+' : $unreadConversations; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Tab Content -->
    <div class="notification-content">
        <!-- @Me Panel -->
        <div id="atMe" class="tab-panel <?php echo $tab === 'atme' ? 'active' : ''; ?>">
            <?php if (empty($mentions)): ?>
                <div class="empty-state">
                    <svg class="iconpark-icon empty-icon" width="48" height="48"><use href="#at-sign"></use></svg>
                    <p><?php echo t('No mentions yet', '暂无@提及'); ?></p>
                </div>
            <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($mentions as $activity): ?>
                        <?php echo renderNotificationItem($activity); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reply Panel -->
        <div id="reply" class="tab-panel <?php echo $tab === 'reply' ? 'active' : ''; ?>">
            <?php if (empty($replies)): ?>
                <div class="empty-state">
                    <svg class="iconpark-icon empty-icon" width="48" height="48"><use href="#comments"></use></svg>
                    <p><?php echo t('No replies yet', '暂无回复通知'); ?></p>
                </div>
            <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($replies as $activity): ?>
                        <?php echo renderNotificationItem($activity); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Message Panel -->
        <div id="message" class="tab-panel <?php echo $tab === 'message' ? 'active' : ''; ?>">
            <!-- Conversation List (full width) -->
            <div class="conversation-list" id="ConversationList" <?php echo $toUser ? 'style="display:none;"' : ''; ?>>
                <?php if (empty($conversations)): ?>
                    <div class="empty-state">
                        <svg class="iconpark-icon empty-icon" width="48" height="48"><use href="#message"></use></svg>
                        <p><?php echo t('No conversations yet', '暂无私信'); ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <?php echo renderConversationItem($conv, $transientKey); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Chat Panel (replaces list when opened) -->
            <div class="chat-panel" id="ChatPanel" <?php echo $toUser ? '' : 'style="display:none;"'; ?>>
                <?php if ($toUser): ?>
                <!-- New conversation to specific user -->
                <div class="chat-header">
                    <button type="button" class="back-btn" id="BackToListBtn">
                        <svg class="iconpark-icon" width="16" height="16"><use href="#left"></use></svg>
                        <span><?php echo t('Back', '返回'); ?></span>
                    </button>
                    <span class="chat-title"><?php echo sprintf(t('ConversationWith', '与 %s 的对话'), htmlspecialchars(val('Name', $toUser, ''))); ?></span>
                </div>
                <div class="chat-messages" id="ChatMessages">
                    <div class="chat-welcome">
                        <p><?php echo sprintf(t('StartConversation', '开始与 %s 的对话'), htmlspecialchars(val('Name', $toUser, ''))); ?></p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <form id="NewMessageForm" data-to-user-id="<?php echo (int)val('UserID', $toUser, 0); ?>" data-to-username="<?php echo htmlspecialchars(val('Name', $toUser, '')); ?>">
                        <input type="hidden" name="TransientKey" value="<?php echo htmlspecialchars($transientKey); ?>">
                        <textarea name="Body" id="MessageBody" placeholder="<?php echo t('Type a message...', '输入消息...'); ?>" rows="2"></textarea>
                        <button type="submit" class="send-btn">
                            <svg class="iconpark-icon" width="18" height="18"><use href="#send"></use></svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Pass data to JavaScript
window.BitsNotification = {
    transientKey: '<?php echo htmlspecialchars($transientKey); ?>',
    currentTab: '<?php echo htmlspecialchars($tab); ?>',
    toUser: <?php echo $toUser ? json_encode($toUser) : 'null'; ?>
};
</script>

<?php
/**
 * Render a notification item.
 *
 * @param array $activity Activity data
 * @return string HTML
 */
function renderNotificationItem($activity) {
    $isUnread = val('IsUnread', $activity, false);
    $userPhoto = htmlspecialchars(val('ActivityUserPhoto', $activity, ''));
    $userUrl = htmlspecialchars(val('ActivityUserUrl', $activity, '#'));
    $userName = htmlspecialchars(val('ActivityUserName', $activity, ''));
    $headlineHtml = val('HeadlineHtml', $activity, '');
    $dateFormatted = val('DateInsertedFormatted', $activity, '');
    $route = val('Route', $activity, '');

    // Build link URL - prefer Route, fallback to user URL
    $linkUrl = !empty($route) ? htmlspecialchars(url($route)) : $userUrl;

    $unreadClass = $isUnread ? ' unread' : '';

    $html = '<div class="notification-item' . $unreadClass . '">';
    $html .= '<a href="' . $userUrl . '" class="notification-avatar">';
    $html .= '<img src="' . $userPhoto . '" alt="' . $userName . '" class="avatar" loading="lazy">';
    if ($isUnread) {
        $html .= '<span class="unread-dot"></span>';
    }
    $html .= '</a>';
    $html .= '<div class="notification-body">';
    $html .= '<div class="notification-content-text">' . $headlineHtml . '</div>';
    $html .= '<div class="notification-meta">';
    $html .= '<time class="notification-time">' . $dateFormatted . '</time>';
    if (!empty($route)) {
        $html .= '<a href="' . $linkUrl . '" class="notification-link">' . t('View', '查看') . '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Render a conversation list item.
 *
 * @param array $conv Conversation data
 * @param string $transientKey CSRF token
 * @return string HTML
 */
function renderConversationItem($conv, $transientKey) {
    $conversationID = val('ConversationID', $conv, 0);
    $otherUserName = htmlspecialchars(val('OtherUserName', $conv, ''));
    $otherUserPhoto = htmlspecialchars(val('OtherUserPhoto', $conv, ''));
    $otherUserUrl = htmlspecialchars(val('OtherUserUrl', $conv, '#'));
    $lastMessageFormatted = val('LastMessageFormatted', $conv, '');
    $hasUnread = val('HasUnread', $conv, false);
    $countNewMessages = val('CountNewMessages', $conv, 0);
    $isGroup = val('IsGroup', $conv, false);

    // Get last message excerpt
    $lastBody = val('LastBody', $conv, '');
    $lastExcerpt = htmlspecialchars(mb_substr(strip_tags(Gdn_Format::text($lastBody)), 0, 50));
    if (mb_strlen($lastBody) > 50) {
        $lastExcerpt .= '...';
    }

    $unreadClass = $hasUnread ? ' has-unread' : '';

    $html = '<div class="conversation-item' . $unreadClass . '" data-conversation-id="' . $conversationID . '">';
    $html .= '<a href="' . $otherUserUrl . '" class="conversation-avatar" onclick="event.stopPropagation();">';
    $html .= '<img src="' . $otherUserPhoto . '" alt="' . $otherUserName . '" class="avatar" loading="lazy">';
    $html .= '</a>';
    $html .= '<div class="conversation-body">';
    $html .= '<div class="conversation-header">';
    $html .= '<span class="conversation-name">' . $otherUserName;
    if ($isGroup) {
        $html .= ' <span class="group-indicator">' . t('Group', '群聊') . '</span>';
    }
    $html .= '</span>';
    $html .= '<time class="conversation-time">' . $lastMessageFormatted . '</time>';
    $html .= '</div>';
    $html .= '<div class="conversation-preview">';
    $html .= '<span class="conversation-excerpt">' . $lastExcerpt . '</span>';
    if ($hasUnread && $countNewMessages > 0) {
        $html .= '<span class="unread-count">' . ($countNewMessages > 99 ? '99+' : $countNewMessages) . '</span>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
?>
