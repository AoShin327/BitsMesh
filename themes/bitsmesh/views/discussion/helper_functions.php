<?php
/**
 * BitsMesh Theme - Discussion Helper Functions
 *
 * Override writeComment() to output modern forum style HTML structure
 * Uses bits-* class naming convention for pixel-level CSS targeting
 *
 * NOTE: Uses if(!function_exists()) pattern to avoid redeclaration errors.
 * This file should be loaded BEFORE the base helper_functions.php.
 *
 * @version 1.4.13
 */

if (!defined('APPLICATION')) {
    exit();
}

if (!function_exists('writeComment')) :
/**
 * Custom writeComment function for BitsMesh theme
 * Outputs modern forum style HTML structure with bits-* classes
 *
 * @param DataSet $comment
 * @param Gdn_Controller $sender
 * @param Gdn_Session $session
 * @param int $currentOffset
 */
function writeComment($comment, $sender, $session, $currentOffset) {
    $comment = (is_array($comment)) ? (object)$comment : $comment;

    $author = Gdn::userModel()->getID($comment->InsertUserID);

    // BitsMesh: Set Offset for commentUrl() to calculate floor number correctly
    $comment->Offset = $currentOffset;
    $permalink = commentUrl($comment, false);

    // Set CanEditComments
    if (!property_exists($sender, 'CanEditComments')) {
        $sender->CanEditComments = $session->checkPermission('Vanilla.Comments.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');
    }

    // Prep event args
    $cssClass = cssClass($comment, false);
    $sender->EventArguments['Comment'] = &$comment;
    $sender->EventArguments['Author'] = &$author;
    $sender->EventArguments['CssClass'] = &$cssClass;
    $sender->EventArguments['CurrentOffset'] = $currentOffset;
    $sender->EventArguments['Permalink'] = $permalink;

    // Get discussion for original poster check
    if ($sender->data('Discussion', null) === null) {
        $discussionModel = new DiscussionModel();
        $discussion = $discussionModel->getID($comment->DiscussionID);
        $sender->setData('Discussion', $discussion);
    }

    // Check if original poster
    $isOriginalPoster = ($sender->data('Discussion.InsertUserID') === $comment->InsertUserID);
    if ($isOriginalPoster) {
        $cssClass .= ' isOriginalPoster';
    }

    // DEPRECATED ARGUMENTS (as of 2.1)
    $sender->EventArguments['Object'] = &$comment;
    $sender->EventArguments['Type'] = 'Comment';

    // Get author info
    $AuthorName = val('Name', $author, t('Unknown'));
    $AuthorUrl = userUrl($author);
    $AuthorPhoto = userPhotoUrl($author);
    $AuthorTitle = val('Title', $author, '');

    // Format dates
    $DateCreated = Gdn_Format::date($comment->DateInserted, 'html');
    $DateCreatedTitle = Gdn_Format::date($comment->DateInserted, '%Y-%m-%d %H:%M:%S');
    $DateISO = date('c', Gdn_Format::toTimestamp($comment->DateInserted));

    // Check if edited
    $DateUpdated = val('DateUpdated', $comment);
    $UpdatedHtml = '';
    if ($DateUpdated) {
        $UpdateUser = val('UpdateUserID', $comment) ? Gdn::userModel()->getID(val('UpdateUserID', $comment)) : $author;
        $UpdateUserName = val('Name', $UpdateUser, t('Unknown'));
        $UpdatedTime = Gdn_Format::date($DateUpdated, 'html');
        $UpdatedTitle = sprintf(t('Edited %s by %s'), Gdn_Format::date($DateUpdated, '%Y-%m-%d %H:%M:%S'), $UpdateUserName);
        $UpdatedHtml = '<span class="bits-date-updated" title="'.htmlspecialchars($UpdatedTitle).'">'.t('edited').' '.$UpdatedTime.'</span>';
    }

    // Floor number equals currentOffset directly
    // (Vanilla increments offset BEFORE calling writeComment, so first comment has offset=1)
    $floorNumber = $currentOffset;
    // Generate floor URL in format: /post-{id}#{floor}
    $discussion = $sender->data('Discussion');
    $floorUrl = $discussion->Url . '#' . $floorNumber;

    // First comment template event
    $sender->fireEvent('BeforeCommentDisplay');
?>
    <li class="<?php echo $cssClass; ?>" id="<?php echo 'Comment_'.$comment->CommentID; ?>" data-comment-id="<?php echo $comment->CommentID; ?>" data-record-type="comment" data-record-id="<?php echo $comment->CommentID; ?>">
        <!-- BitsMesh: Floor anchor for /post-{id}#{floor} URL format -->
        <span id="<?php echo $floorNumber; ?>"></span>
        <div class="Comment">
            <?php
            // Write a stub for the latest comment so it's easy to link to it from outside.
            if ($currentOffset == Gdn::controller()->data('_LatestItem')) {
                echo '<span id="latest"></span>';
            }
            ?>

            <div class="Options">
                <?php writeCommentOptions($comment); ?>
            </div>

            <?php $sender->fireEvent('BeforeCommentMeta'); ?>

            <!-- BitsMesh Modern Forum Style: Content Item -->
            <section class="bits-content-item">
                <div class="bits-content-meta-info">
                    <div class="bits-avatar-wrapper">
                        <a title="<?php echo htmlspecialchars($AuthorName); ?>" href="<?php echo $AuthorUrl; ?>">
                            <img src="<?php echo $AuthorPhoto; ?>" alt="<?php echo htmlspecialchars($AuthorName); ?>" class="bits-avatar">
                        </a>
                    </div>
                    <div class="bits-author-container">
                        <div class="bits-author-info">
                            <a href="<?php echo $AuthorUrl; ?>" class="bits-author-name"><?php echo htmlspecialchars($AuthorName); ?></a>
                            <?php if ($isOriginalPoster): ?>
                                <span class="bits-is-poster bits-role-tag bits-badge"><?php echo t('楼主'); ?></span>
                            <?php endif; ?>
                            <?php
                            // Author title/rank if exists
                            if ($AuthorTitle) {
                                echo '<span class="bits-author-title bits-badge">'.htmlspecialchars($AuthorTitle).'</span>';
                            }
                            $sender->fireEvent('AuthorInfo');
                            ?>
                        </div>
                        <div class="bits-content-info">
                            <span class="bits-date-created">
                                <a href="<?php echo htmlspecialchars($permalink); ?>" name="Item_<?php echo $currentOffset; ?>" rel="nofollow">
                                    <time title="<?php echo $DateCreatedTitle; ?>" datetime="<?php echo $DateISO; ?>"><?php echo $DateCreated; ?></time>
                                </a>
                            </span>
                            <?php echo $UpdatedHtml; ?>
                            <?php
                            // IP Address for admins
                            if ($session->checkPermission('Garden.PersonalInfo.View') && val('InsertIPAddress', $comment)) {
                                echo '<span class="bits-ip-address">'.ipAnchor($comment->InsertIPAddress).'</span>';
                            }
                            $sender->fireEvent('CommentInfo');
                            ?>
                        </div>
                    </div>
                    <div class="bits-floor-link-wrapper">
                        <a href="<?php echo htmlspecialchars($floorUrl); ?>" class="bits-floor-link" title="<?php echo sprintf(t('第 %d 楼'), $floorNumber); ?>">#<?php echo $floorNumber; ?></a>
                    </div>
                </div>

                <article class="bits-post-content">
                    <?php echo formatBody($comment); ?>
                </article>

                <?php
                // BitsMesh: User Signature Display
                $signature = val('Signature', $author, '');
                if (!empty($signature)):
                ?>
                <div class="bits-user-signature">
                    <div class="bits-signature-divider"></div>
                    <div class="bits-signature-content UserContent">
                        <?php echo Gdn_Format::to($signature, 'Markdown'); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                $sender->fireEvent('AfterCommentBody');

                // BitsMesh: Modern Forum Style Comment Menu
                // Like NodeSeek: icon + count only, text shown via title attribute
                // Comments show 3 items (no bookmark), logged-in users get quote and reply
                $isLoggedIn = $session->isValid();
                $quoteUrl = url("/post/quote/{$comment->DiscussionID}/Comment_{$comment->CommentID}");
                ?>
                <div class="comment-menu">
                    <div class="menu-item menu-like" title="<?php echo t('点赞'); ?>">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#good-one"></use></svg>
                        <span>0</span>
                    </div>
                    <div class="menu-item menu-credit" title="<?php echo t('加鸡腿'); ?>">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#chicken-leg"></use></svg>
                        <span>0</span>
                    </div>
                    <div class="menu-item menu-dislike" title="<?php echo t('反对'); ?>">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#bad-one"></use></svg>
                        <span>0</span>
                    </div>
                    <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $quoteUrl; ?>" class="menu-item menu-quote ReactButton Quote" title="<?php echo t('引用'); ?>">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#quote"></use></svg>
                        <span><?php echo t('引用'); ?></span>
                    </a>
                    <a href="#CommentForm" class="menu-item menu-reply bits-reply-btn" title="<?php echo t('回复'); ?>"
                       data-author="<?php echo htmlspecialchars($AuthorName); ?>"
                       data-floor="<?php echo $floorNumber; ?>"
                       data-floor-url="<?php echo htmlspecialchars($floorUrl); ?>">
                        <svg class="iconpark-icon" width="12" height="12"><use href="#back"></use></svg>
                        <span><?php echo t('回复'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php

                // Attachments
                if (val('Attachments', $comment) && function_exists('writeAttachments')) {
                    echo '<div class="bits-attachments-wrap">';
                    writeAttachments($comment->Attachments);
                    echo '</div>';
                }
                ?>
            </section>
        </div>
    </li>
<?php
    $sender->fireEvent('AfterComment');
}
endif;

// Load base helper functions for other required functions (writeBookmarkLink, etc.)
// Our writeComment is already defined, so the base file's version will be skipped
$baseHelperPath = PATH_APPLICATIONS.'/vanilla/views/discussion/helper_functions.php';
if (file_exists($baseHelperPath)) {
    include_once $baseHelperPath;
}
