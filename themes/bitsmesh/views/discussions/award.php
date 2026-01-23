<?php if (!defined('APPLICATION')) exit();
/**
 * Award (Featured) Page View
 *
 * Displays all announced/featured discussions.
 *
 * @package BitsMesh
 * @since 1.0
 */

// Include helper functions
include_once $this->fetchViewLocation('helper_functions', 'discussions');

$discussions = $this->data('Discussions');
$countDiscussions = (int)$this->data('CountDiscussions', 0);
$pagerUrl = $this->data('_PagerUrl', '/award/{Page}');
$currentPage = (int)$this->data('_Page', 1);
$perPage = (int)$this->data('_Limit', 30);
$session = Gdn::session();
?>

<div class="award-page">
    <!-- Page Header -->
    <div class="award-page-header">
        <h1>
            <svg class="iconpark-icon" width="24" height="24"><use href="#diamonds"></use></svg>
            <?php echo t('Featured', '推荐阅读'); ?>
        </h1>
        <span class="award-page-description">
            <?php echo t('Featured.Description', '精选优质内容，值得一读'); ?>
        </span>
    </div>

    <!-- Discussions List -->
    <ul class="DataList Discussions">
        <?php
        if ($discussions && $discussions->numRows() > 0) {
            foreach ($discussions as $discussion) {
                writeDiscussion($discussion, $this, $session);
            }
        } else {
            echo '<li class="Empty">';
            echo t('NoFeaturedDiscussions', '暂无推荐内容');
            echo '</li>';
        }
        ?>
    </ul>

    <?php
    // Pagination
    if ($countDiscussions > $perPage) {
        $totalPages = ceil($countDiscussions / $perPage);
        ?>
        <div class="bits-pager">
            <?php
            // Previous page link
            if ($currentPage > 1) {
                $prevUrl = str_replace('{Page}', 'p' . ($currentPage - 1), $pagerUrl);
                echo '<a href="' . htmlspecialchars($prevUrl) . '" class="pager-prev" rel="prev">';
                echo '<svg class="iconpark-icon" width="12" height="12"><use href="#left"></use></svg>';
                echo '</a>';
            }

            // Page numbers
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);

            if ($startPage > 1) {
                echo '<a href="' . htmlspecialchars(str_replace('{Page}', 'p1', $pagerUrl)) . '" class="pager-pos">1</a>';
                if ($startPage > 2) {
                    echo '<span class="pager-pos ellipsis">..</span>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    echo '<span class="pager-pos pager-cur">' . $i . '</span>';
                } else {
                    $pageUrl = str_replace('{Page}', 'p' . $i, $pagerUrl);
                    echo '<a href="' . htmlspecialchars($pageUrl) . '" class="pager-pos">' . $i . '</a>';
                }
            }

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="pager-pos ellipsis">..</span>';
                }
                echo '<a href="' . htmlspecialchars(str_replace('{Page}', 'p' . $totalPages, $pagerUrl)) . '" class="pager-pos">' . $totalPages . '</a>';
            }

            // Next page link
            if ($currentPage < $totalPages) {
                $nextUrl = str_replace('{Page}', 'p' . ($currentPage + 1), $pagerUrl);
                echo '<a href="' . htmlspecialchars($nextUrl) . '" class="pager-next" rel="next">';
                echo '<svg class="iconpark-icon" width="12" height="12"><use href="#right"></use></svg>';
                echo '</a>';
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>
