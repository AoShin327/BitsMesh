<?php
/**
 * BitsMesh Conversation Messages View
 *
 * AJAX-loaded chat panel content for private messages.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

// Get data from controller
$conversation = $this->data('Conversation', []);
$messages = $this->data('Messages', []);

$conversationID = val('ConversationID', $conversation, 0);
$transientKey = Gdn::session()->transientKey();
$currentUserID = Gdn::session()->UserID;
?>

<div class="chat-container" data-conversation-id="<?php echo $conversationID; ?>">
    <!-- Chat Header -->
    <div class="chat-header">
        <button type="button" class="back-btn" id="BackToListBtn">
            <svg class="iconpark-icon" width="16" height="16"><use href="#left"></use></svg>
            <span><?php echo t('Back', '返回'); ?></span>
        </button>
        <?php
        // Get other user name from controller data
        $otherUserName = val('OtherUserName', $conversation, '');
        ?>
        <span class="chat-title"><?php echo sprintf(t('ConversationWith', '与 %s 的对话'), htmlspecialchars($otherUserName)); ?></span>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages" id="ChatMessages">
        <?php if (empty($messages)): ?>
            <div class="chat-empty">
                <p><?php echo t('No messages yet. Start the conversation!', '暂无消息，开始聊天吧！'); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <?php
                $isSent = val('IsSent', $msg, false);
                $bubbleClass = $isSent ? 'sent' : 'received';
                $userName = htmlspecialchars(val('InsertUserName', $msg, ''));
                $userPhoto = htmlspecialchars(val('InsertUserPhoto', $msg, ''));
                $userUrl = htmlspecialchars(val('InsertUserUrl', $msg, '#'));
                $bodyFormatted = val('BodyFormatted', $msg, '');
                $dateFormatted = val('DateInsertedFormatted', $msg, '');
                ?>
                <div class="message-row <?php echo $bubbleClass; ?>">
                    <?php if (!$isSent): ?>
                        <a href="<?php echo $userUrl; ?>" class="message-avatar">
                            <img src="<?php echo $userPhoto; ?>" alt="<?php echo $userName; ?>" class="avatar" loading="lazy">
                        </a>
                    <?php endif; ?>
                    <div class="message-content">
                        <?php if (!$isSent): ?>
                            <div class="message-sender">
                                <a href="<?php echo $userUrl; ?>"><?php echo $userName; ?></a>
                            </div>
                        <?php endif; ?>
                        <div class="message-bubble <?php echo $bubbleClass; ?>">
                            <div class="message-text"><?php echo $bodyFormatted; ?></div>
                        </div>
                        <div class="message-time"><?php echo $dateFormatted; ?></div>
                    </div>
                    <?php if ($isSent): ?>
                        <a href="<?php echo $userUrl; ?>" class="message-avatar">
                            <img src="<?php echo $userPhoto; ?>" alt="<?php echo $userName; ?>" class="avatar" loading="lazy">
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message Input -->
    <div class="chat-input-area">
        <form id="SendMessageForm" class="message-form">
            <input type="hidden" name="TransientKey" value="<?php echo htmlspecialchars($transientKey); ?>">
            <input type="hidden" name="ConversationID" value="<?php echo $conversationID; ?>">
            <textarea name="Body" id="MessageBody" class="message-input" placeholder="<?php echo t('Type a message...', '输入消息...'); ?>" rows="1" maxlength="5000"></textarea>
            <button type="submit" class="send-btn" title="<?php echo t('Send', '发送'); ?>">
                <svg class="iconpark-icon" width="20" height="20"><use href="#send-one"></use></svg>
            </button>
        </form>
    </div>
</div>
