<?php
/**
 * Smarty function to generate category menu links for navigation
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package vanilla-smarty
 */

/**
 * Generate navigation menu links for top-level categories
 *
 * @param array $params Parameters passed to the function
 * @param object $smarty Smarty instance
 * @return string HTML output
 */
function smarty_function_category_menu($params, &$smarty) {
    $depth = val('depth', $params, 1);
    $maxItems = val('max', $params, 10);

    // Get categories
    $categoryModel = new CategoryModel();
    $categories = CategoryModel::categories();

    $output = '';
    $count = 0;

    // Get current category ID if available
    $currentCategoryID = null;
    $controller = Gdn::controller();
    if ($controller) {
        $currentCategoryID = $controller->data('Category.CategoryID');
        if (!$currentCategoryID && isset($controller->CategoryID)) {
            $currentCategoryID = $controller->CategoryID;
        }
    }

    foreach ($categories as $category) {
        // Only show categories at the specified depth
        if (val('Depth', $category) != $depth) {
            continue;
        }

        // Skip hidden categories
        if (val('DisplayAs', $category) === 'Heading') {
            continue;
        }

        // Check permission
        $categoryID = val('CategoryID', $category);
        if (!CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.View')) {
            continue;
        }

        $name = htmlspecialchars(val('Name', $category));
        $url = categoryUrl($category);
        $isCurrent = ($categoryID == $currentCategoryID);
        $cssClass = $isCurrent ? 'current-category' : '';
        $ariaCurrent = $isCurrent ? ' aria-current="page"' : '';

        $output .= "<li class=\"{$cssClass}\"><a href=\"{$url}\"{$ariaCurrent}>{$name}</a></li>\n";

        $count++;
        if ($count >= $maxItems) {
            break;
        }
    }

    return $output;
}
