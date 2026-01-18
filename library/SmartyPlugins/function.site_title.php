<?php
/**
 * Smarty function to get site title
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package vanilla-smarty
 */

/**
 * Returns the site title (Garden.Title config value)
 *
 * @param array $params Parameters passed to the function
 * @param object $smarty Smarty instance
 * @return string Site title
 */
function smarty_function_site_title($params, &$smarty) {
    return htmlspecialchars(c('Garden.Title', 'Vanilla'));
}
