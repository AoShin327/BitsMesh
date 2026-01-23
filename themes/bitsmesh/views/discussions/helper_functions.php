<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh Theme - Discussion List Helper Functions
 *
 * Override of applications/vanilla/views/discussions/helper_functions.php
 * Adds SVG icons to Meta Discussion items using IconPark sprite.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

/**
 * SVG icon helper - generates inline SVG using sprite reference.
 *
 * @param string $iconId The icon ID in the sprite (e.g., 'icon-eyes').
 * @param int $size Icon size in pixels (default: 12).
 * @return string SVG HTML markup.
 */
function bitsMetaIcon($iconId, $size = 12) {
    return '<svg class="bits-meta-icon" width="'.$size.'" height="'.$size.'"><use href="#'.$iconId.'"></use></svg>';
}

if (!function_exists('AdminCheck')) {
    /**
     * @param null $discussion
     * @param bool|FALSE $wrap
     * @return string
     */
    function adminCheck($discussion = null, $wrap = FALSE) {
        static $useAdminChecks = NULL;
        if ($useAdminChecks === null) {
            $useAdminChecks = c('Vanilla.AdminCheckboxes.Use') && Gdn::session()->checkPermission('Garden.Moderation.Manage');
        }
        if (!$useAdminChecks) {
            return '';
        }

        static $canEdits = [], $checked = NULL;
        $result = '';

        if ($discussion) {
            if (!isset($canEdits[$discussion->CategoryID])) {
                $canEdits[$discussion->CategoryID] = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID));
            }

            if ($canEdits[$discussion->CategoryID]) {
                if ($checked === null) {
                    $checked = (array)Gdn::session()->getAttribute('CheckedDiscussions', []);
                    if (!is_array($checked)) {
                        $checked = [];
                    }
                }

                if (in_array($discussion->DiscussionID, $checked))
                    $itemSelected = ' checked="checked"';
                else
                    $itemSelected = '';

                $result = <<<EOT
<span class="AdminCheck"><input type="checkbox" name="DiscussionID[]" value="{$discussion->DiscussionID}" $itemSelected /></span>
EOT;
            }
        } else {
            $result = '<span class="AdminCheck"><input type="checkbox" name="Toggle" /></span>';
        }

        if ($wrap) {
            $result = $wrap[0].$result.$wrap[1];
        }

        return $result;
    }
}

if (!function_exists('BookmarkButton')) {
    /**
     * @param $discussion
     * @return string
     */
    function bookmarkButton($discussion) {
        if (!Gdn::session()->isValid()) {
            return '';
        }

        $title = t($discussion->Bookmarked == '1' ? 'Unbookmark' : 'Bookmark');
        return anchor(
            $title,
            '/discussion/bookmark/'.$discussion->DiscussionID.'/'.Gdn::session()->transientKey(),
            $discussion->Bookmarked == '1' ? 'Bookmarked' : '',
            ['title' => $title]
        );
    }
}

if (!function_exists('CategoryLink')) :
    /**
     * @param $discussion
     * @param string $prefix
     * @return string
     */
    function categoryLink($discussion, $prefix = ' ') {
        $category = CategoryModel::categories(val('CategoryID', $discussion));
        if ($category) {
            return wrap($prefix.anchor(htmlspecialchars($category['Name']), $category['Url']), 'span', ['class' => 'MItem Category']);
        }
    }
endif;

if (!function_exists('DiscussionHeading')) :
    /**
     * @return string
     */
    function discussionHeading() {
        return t('Discussion');
    }
endif;

if (!function_exists('WriteDiscussion')) :
    /**
     * BitsMesh Override: Write discussion list item with SVG icons.
     *
     * @param $discussion
     * @param $sender
     * @param $session
     */
    function writeDiscussion($discussion, $sender, $session) {
        $cssClass = cssClass($discussion);
        $discussionUrl = $discussion->Url;
        $category = CategoryModel::categories($discussion->CategoryID);

        // BitsMesh: 移除 #latest 锚点，让用户直接看到帖子顶部（主帖内容）
        // 原代码: if ($session->UserID) { $discussionUrl .= '#latest'; }
        $sender->EventArguments['DiscussionUrl'] = &$discussionUrl;
        $sender->EventArguments['Discussion'] = &$discussion;
        $sender->EventArguments['CssClass'] = &$cssClass;

        $first = userBuilder($discussion, 'First');
        $last = userBuilder($discussion, 'Last');
        $sender->EventArguments['FirstUser'] = &$first;
        $sender->EventArguments['LastUser'] = &$last;

        $sender->fireEvent('BeforeDiscussionName');

        $discussionName = $discussion->Name;
        if (!preg_match('/\w/u', $discussionName)) {
            $discussionName = t('Blank Discussion Topic');
        }
        $sender->EventArguments['DiscussionName'] = &$discussionName;

        static $firstDiscussion = true;
        if (!$firstDiscussion) {
            $sender->fireEvent('BetweenDiscussion');
        } else {
            $firstDiscussion = false;
        }

        $discussion->CountPages = ceil($discussion->CountComments / $sender->CountCommentsPerPage);
        ?>
        <li id="Discussion_<?php echo $discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID)) && c('Vanilla.AdminCheckboxes.Use');
            }
            $sender->fireEvent('BeforeDiscussionContent');

            // AdminCheck - 放在帖子行最前面（头像左侧）
            echo adminCheck($discussion);
            ?>
            <span class="Options">
                <?php
                echo optionsList($discussion);
                echo bookmarkButton($discussion);
                ?>
            </span>

            <div class="ItemContent Discussion">
                <div class="Title" role="heading" aria-level="3">
                    <?php
                    echo anchor($discussionName, $discussionUrl);
                    // Award icon (推荐阅读) - 使用 Announce 字段判断
                    if (!empty($discussion->Announce) && $discussion->Announce > 0) {
                        echo ' <a href="/award" class="bits-award-icon" title="'.htmlspecialchars(t('Featured', '推荐阅读')).'">';
                        echo '<svg class="iconpark-icon" width="15" height="15"><use href="#diamonds"></use></svg>';
                        echo '</a>';
                    }
                    $sender->fireEvent('AfterDiscussionTitle');
                    ?>
                </div>
                <div class="Meta Meta-Discussion">
                    <?php
                    writeTags($discussion);

                    // DiscussionAuthor - 楼主 (icon-user) - 放在最左边
                    echo '<span class="MItem DiscussionAuthor">'.bitsMetaIcon('icon-user').userAnchor($first).'</span>';
                    ?>
                    <span class="MItem MCount ViewCount"><?php
                        echo bitsMetaIcon('icon-eyes');
                        echo number_format($discussion->CountViews);
                        ?></span>
                    <span class="MItem MCount CommentCount"><?php
                        echo bitsMetaIcon('icon-comments');
                        echo number_format($discussion->CountComments);
                        ?></span>
                    <span class="MItem MCount DiscussionScore Hidden"><?php
                        $score = $discussion->Score;
                        if ($score == '') $score = 0;
                        printf(plural($score,
                            '%s point', '%s points',
                            bigPlural($score, '%s point')));
                        ?></span>
                    <?php
                    echo newComments($discussion);

                    $sender->fireEvent('AfterCountMeta');

                    if ($discussion->LastCommentID != '') {
                        // LastCommentBy - 最后回复者 (icon-lightning)
                        echo ' <span class="MItem LastCommentBy">'.bitsMetaIcon('icon-lightning').userAnchor($last).'</span> ';
                        echo ' <span class="MItem LastCommentDate">'.bitsMetaIcon('icon-time').Gdn_Format::date($discussion->LastDate, 'html').'</span>';
                    } else {
                        // 无回复时只显示发帖时间
                        echo ' <span class="MItem LastCommentDate">'.bitsMetaIcon('icon-time').Gdn_Format::date($discussion->FirstDate, 'html');
                        if ($source = val('Source', $discussion)) {
                            echo ' '.sprintf(t('via %s'), t($source.' Source', $source));
                        }
                        echo '</span> ';
                    }

                    if ($sender->data('_ShowCategoryLink', true) && $category && c('Vanilla.Categories.Use') &&
                        CategoryModel::checkPermission($category, 'Vanilla.Discussions.View')) {

                        echo wrap(
                            anchor(htmlspecialchars($discussion->Category),
                            categoryUrl($discussion->CategoryUrlCode)),
                            'span',
                            ['class' => 'MItem Category '.$category['CssClass']]
                        );
                    }
                    $sender->fireEvent('DiscussionMeta');
                    ?>
                </div>
            </div>
            <?php $sender->fireEvent('AfterDiscussionContent'); ?>
        </li>
    <?php
    }
endif;

if (!function_exists('WriteDiscussionSorter')) :
    /**
     * @param null $selected
     * @param null $options
     */
    function writeDiscussionSorter($selected = null, $options = null) {
        deprecated('writeDiscussionSorter', 'DiscussionSortFilterModule', 'March 2016');

        if ($selected === null) {
            $selected = Gdn::session()->getPreference('Discussions.SortField', 'DateLastComment');
        }
        $selected = stringBeginsWith($selected, 'd.', TRUE, true);

        $options = [
            'DateLastComment' => t('Sort by Last Comment', 'by Last Comment'),
            'DateInserted' => t('Sort by Start Date', 'by Start Date')
        ];

        ?>
        <span class="ToggleFlyout SelectFlyout">
        <?php
        if (isset($options[$selected])) {
            $text = $options[$selected];
        } else {
            $text = reset($options);
        }
        echo wrap($text.' '.sprite('', 'DropHandle'), 'span', ['class' => 'Selected']);
        ?>
            <div class="Flyout MenuItems">
                <ul>
                    <?php
                    foreach ($options as $sortField => $sortText) {
                        echo wrap(anchor($sortText, '#', ['class' => 'SortDiscussions', 'data-field' => $sortField]), 'li');
                    }
                    ?>
                </ul>
            </div>
         </span>
        <?php
    }
endif;

if (!function_exists('WriteMiniPager')) :
    /**
     * @param $discussion
     */
    function writeMiniPager($discussion) {
        if (!property_exists($discussion, 'CountPages')) {
            return;
        }

        if ($discussion->CountPages > 1) {
            echo '<span class="MiniPager">';
            if ($discussion->CountPages < 5) {
                for ($i = 0; $i < $discussion->CountPages; $i++) {
                    writePageLink($discussion, $i + 1);
                }
            } else {
                writePageLink($discussion, 1);
                writePageLink($discussion, 2);
                echo '<span class="Elipsis">...</span>';
                writePageLink($discussion, $discussion->CountPages - 1);
                writePageLink($discussion, $discussion->CountPages);
            }
            echo '</span>';
        }
    }
endif;

if (!function_exists('WritePageLink')):
    /**
     * @param $discussion
     * @param $pageNumber
     */
    function writePageLink($discussion, $pageNumber) {
        echo anchor($pageNumber, discussionUrl($discussion, $pageNumber));
    }
endif;

if (!function_exists('NewComments')) :
    /**
     * @param $discussion
     * @return string
     */
    function newComments($discussion) {
        if (!Gdn::session()->isValid())
            return '';

        if ($discussion->CountUnreadComments === TRUE) {
            $title = htmlspecialchars(t("You haven't read this yet."));
            return ' <strong class="HasNew JustNew NewCommentCount" title="'.$title.'">'.t('new discussion', 'new').'</strong>';
        } elseif ($discussion->CountUnreadComments > 0) {
            $title = htmlspecialchars(plural($discussion->CountUnreadComments, "%s new comment since you last read this.", "%s new comments since you last read this."));
            return ' <strong class="HasNew NewCommentCount" title="'.$title.'">'.plural($discussion->CountUnreadComments, '%s new', '%s new plural', bigPlural($discussion->CountUnreadComments, '%s new', '%s new plural')).'</strong>';
        }
        return '';
    }
endif;

if (!function_exists('tag')) :
    /**
     * @param $discussion
     * @param $column
     * @param $code
     * @param bool|false $cssClass
     * @return string|void
     */
    function tag($discussion, $column, $code, $cssClass = FALSE) {
        $discussion = (object)$discussion;

        if (is_numeric($discussion->$column) && !$discussion->$column)
            return '';
        if (!is_numeric($discussion->$column) && strcasecmp($discussion->$column, $code) != 0)
            return;

        if (!$cssClass)
            $cssClass = "Tag-$code";

        return ' <span class="Tag '.$cssClass.'" title="'.htmlspecialchars(t($code)).'">'.t($code).'</span> ';
    }
endif;

if (!function_exists('writeTags')) :
    /**
     * Write discussion tags (Closed only, Award is shown in title).
     *
     * @param $discussion
     * @throws Exception
     */
    function writeTags($discussion) {
        Gdn::controller()->fireEvent('BeforeDiscussionMeta');

        // Award (原 Announcement) 已移到标题区域显示，这里只保留 Closed 标签
        echo tag($discussion, 'Closed', 'Closed');

        Gdn::controller()->fireEvent('AfterDiscussionLabels');
    }
endif;

if (!function_exists('writeFilterTabs')) :
    /**
     * @param $sender
     */
    function writeFilterTabs($sender) {
        $session = Gdn::session();
        $title = property_exists($sender, 'Category') ? val('Name', $sender->Category, '') : '';
        if ($title == '') {
            $title = t('All Discussions');
        }
        $bookmarked = t('My Bookmarks');
        $myDiscussions = t('My Discussions');
        $myDrafts = t('My Drafts');
        $countBookmarks = 0;
        $countDiscussions = 0;
        $countDrafts = 0;

        if ($session->isValid()) {
            $countBookmarks = $session->User->CountBookmarks;
            $countDiscussions = $session->User->CountDiscussions;
            $countDrafts = $session->User->CountDrafts;
        }

        if (c('Vanilla.Discussions.ShowCounts', true)) {
            $bookmarked .= countString($countBookmarks, '/discussions/UserBookmarkCount');
            $myDiscussions .= countString($countDiscussions);
            $myDrafts .= countString($countDrafts);
        }

        ?>
        <div class="Tabs DiscussionsTabs">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = $session->checkPermission('Vanilla.Discussions.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');
            }
            if ($sender->CanEditDiscussions) {
                ?>
                <span class="Options"><span class="AdminCheck">
                    <input type="checkbox" name="Toggle"/>
                </span></span>
            <?php } ?>
            <ul>
                <?php $sender->fireEvent('BeforeDiscussionTabs'); ?>
                <li<?php echo strtolower($sender->ControllerName) == 'discussionscontroller' && strtolower($sender->RequestMethod) == 'index' ? ' class="Active"' : ''; ?>><?php echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?></li>
                <?php $sender->fireEvent('AfterAllDiscussionsTab'); ?>

                <?php
                if (c('Vanilla.Categories.ShowTabs')) {
                    $cssClass = '';
                    if (strtolower($sender->ControllerName) == 'categoriescontroller' && strtolower($sender->RequestMethod) == 'all') {
                        $cssClass = 'Active';
                    }

                    echo " <li class=\"$cssClass\">".anchor(t('Categories'), '/categories/all', 'TabLink').'</li> ';
                }
                ?>
                <?php if ($countBookmarks > 0 || $sender->RequestMethod == 'bookmarked') { ?>
                    <li<?php echo $sender->RequestMethod == 'bookmarked' ? ' class="Active"' : ''; ?>><?php echo anchor($bookmarked, '/discussions/bookmarked', 'MyBookmarks TabLink'); ?></li>
                    <?php
                    $sender->fireEvent('AfterBookmarksTab');
                }
                if (($countDiscussions > 0 || $sender->RequestMethod == 'mine') && c('Vanilla.Discussions.ShowMineTab', true)) {
                    ?>
                    <li<?php echo $sender->RequestMethod == 'mine' ? ' class="Active"' : ''; ?>><?php echo anchor($myDiscussions, '/discussions/mine', 'MyDiscussions TabLink'); ?></li>
                <?php
                }
                if ($countDrafts > 0 || $sender->ControllerName == 'draftscontroller') {
                    ?>
                    <li<?php echo $sender->ControllerName == 'draftscontroller' ? ' class="Active"' : ''; ?>><?php echo anchor($myDrafts, '/drafts', 'MyDrafts TabLink'); ?></li>
                <?php
                }
                $sender->fireEvent('AfterDiscussionTabs');
                ?>
            </ul>
        </div>
    <?php
    }
endif;

if (!function_exists('optionsList')) :
    /**
     * Build HTML for discussions options menu.
     *
     * @param $discussion
     * @return DropdownModule|string
     * @throws Exception
     */
    function optionsList($discussion) {
        if (Gdn::session()->isValid() && !empty(Gdn::controller()->ShowOptions)) {
            include_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussion', 'vanilla');
            return getDiscussionOptionsDropdown($discussion);
        }
        return '';
    }
endif;

if (!function_exists('writeOptions')) :
    /**
     * Render options that the user has for this discussion.
     */
    function writeOptions($discussion) {
        if (!Gdn::session()->isValid() || !Gdn::controller()->ShowOptions)
            return;

        echo '<span class="Options">';
        echo optionsList($discussion);
        echo bookmarkButton($discussion);
        echo adminCheck($discussion);
        echo '</span>';
    }
endif;
