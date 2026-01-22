<?php
/**
 * BitsMesh Theme - Discussion Template
 *
 * Pixel-level replication of modern forum post detail page
 * HTML structure follows NodeSeek pattern with bits-* class naming
 *
 * @version 1.4.13
 */

if (!defined('APPLICATION')) {
    exit();
}

$Discussion = $this->data('Discussion');
$Author = Gdn::userModel()->getID($Discussion->InsertUserID);

// Prep event args
$CssClass = cssClass($Discussion, false);
$this->EventArguments['Discussion'] = &$Discussion;
$this->EventArguments['Author'] = &$Author;
$this->EventArguments['CssClass'] = &$CssClass;

// DEPRECATED ARGUMENTS (as of 2.1)
$this->EventArguments['Object'] = &$Discussion;
$this->EventArguments['Type'] = 'Discussion';

// Discussion template event
$this->fireEvent('BeforeDiscussionDisplay');

// Get author info
$AuthorName = val('Name', $Author, t('Unknown'));
$AuthorUrl = userUrl($Author);
$AuthorPhoto = userPhotoUrl($Author);
$AuthorTitle = val('Title', $Author, '');

// Format dates
$DateCreated = Gdn_Format::date($Discussion->DateInserted, 'html');
$DateCreatedTitle = Gdn_Format::date($Discussion->DateInserted, '%Y-%m-%d %H:%M:%S');
$DateISO = date('c', Gdn_Format::toTimestamp($Discussion->DateInserted));

// Check if edited
$DateUpdated = val('DateUpdated', $Discussion);
$UpdatedHtml = '';
if ($DateUpdated) {
    $UpdateUser = val('UpdateUserID', $Discussion) ? Gdn::userModel()->getID(val('UpdateUserID', $Discussion)) : $Author;
    $UpdateUserName = val('Name', $UpdateUser, t('Unknown'));
    $UpdatedTime = Gdn_Format::date($DateUpdated, 'html');
    $UpdatedTitle = sprintf(t('Edited %s by %s'), Gdn_Format::date($DateUpdated, '%Y-%m-%d %H:%M:%S'), $UpdateUserName);
    $UpdatedHtml = '<span class="bits-date-updated" title="'.htmlspecialchars($UpdatedTitle).'">'.t('edited').' '.$UpdatedTime.'</span>';
}

// Category info
$CategoryHtml = '';
if (c('Vanilla.Categories.Use')) {
    $CategoryName = htmlspecialchars($this->data('Discussion.Category'));
    $CategoryUrl = categoryUrl($this->data('Discussion.CategoryUrlCode'));
    $CategoryHtml = '<span class="bits-content-category"> '.t('in').' <a href="'.$CategoryUrl.'">'.$CategoryName.'</a></span>';
}

// Floor link URL
$FloorUrl = $Discussion->Url.'#0';
?>
<div id="<?php echo 'Discussion_'.$Discussion->DiscussionID; ?>" class="<?php echo $CssClass; ?> ItemDiscussion">
    <!-- BitsMesh: Floor anchor for /post-{id}#0 URL format -->
    <span id="0"></span>
    <div class="Discussion">
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
                        <span class="bits-is-poster bits-role-tag bits-badge"><?php echo t('楼主'); ?></span>
                        <?php
                        // Author title/rank if exists
                        if ($AuthorTitle) {
                            echo '<span class="bits-author-title bits-badge">'.htmlspecialchars($AuthorTitle).'</span>';
                        }
                        ?>
                    </div>
                    <div class="bits-content-info">
                        <span class="bits-date-created">
                            <time title="<?php echo $DateCreatedTitle; ?>" datetime="<?php echo $DateISO; ?>"><?php echo $DateCreated; ?></time>
                        </span>
                        <?php echo $UpdatedHtml; ?>
                        <?php echo $CategoryHtml; ?>
                        <?php
                        // IP Address for admins
                        $Session = Gdn::session();
                        if ($Session->checkPermission('Garden.PersonalInfo.View') && val('InsertIPAddress', $Discussion)) {
                            echo '<span class="bits-ip-address">'.ipAnchor($Discussion->InsertIPAddress).'</span>';
                        }
                        $this->fireEvent('DiscussionInfo');
                        ?>
                    </div>
                </div>
                <div class="bits-floor-link-wrapper">
                    <a href="<?php echo htmlspecialchars($FloorUrl); ?>" class="bits-floor-link" title="<?php echo t('主楼'); ?>">#0</a>
                </div>
            </div>

            <?php $this->fireEvent('BeforeDiscussionBody'); ?>

            <article class="bits-post-content">
                <?php echo formatBody($Discussion); ?>
            </article>

            <?php
            $this->fireEvent('AfterDiscussionBody');

            // BitsMesh: Modern Forum Style Comment Menu
            // Like NodeSeek: icon + count only, text shown via title attribute
            // Logged-in users get additional quote and reply buttons
            $isLoggedIn = Gdn::session()->isValid();
            $quoteUrl = url("/post/quote/{$Discussion->DiscussionID}/Discussion_{$Discussion->DiscussionID}");
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
                <div class="menu-item menu-bookmark" title="<?php echo t('收藏'); ?>">
                    <svg class="iconpark-icon" width="12" height="12"><use href="#star"></use></svg>
                    <span>0</span>
                </div>
                <?php if ($isLoggedIn): ?>
                <a href="<?php echo $quoteUrl; ?>" class="menu-item menu-quote ReactButton Quote" title="<?php echo t('引用'); ?>">
                    <svg class="iconpark-icon" width="12" height="12"><use href="#quote"></use></svg>
                    <span><?php echo t('引用'); ?></span>
                </a>
                <a href="#CommentForm" class="menu-item menu-reply bits-reply-btn" title="<?php echo t('回复'); ?>"
                   data-author="<?php echo htmlspecialchars($AuthorName); ?>"
                   data-floor="0"
                   data-floor-url="<?php echo htmlspecialchars($FloorUrl); ?>">
                    <svg class="iconpark-icon" width="12" height="12"><use href="#back"></use></svg>
                    <span><?php echo t('回复'); ?></span>
                </a>
                <?php endif; ?>
            </div>
            <?php

            // Attachments
            if (val('Attachments', $Discussion) && function_exists('writeAttachments')) {
                echo '<div class="bits-attachments-wrap">';
                writeAttachments($Discussion->Attachments);
                echo '</div>';
            }
            ?>
        </section>
    </div>
</div>
