<?php
/**
 * Smarty function to get site logo URL
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package vanilla-smarty
 */

/**
 * Returns the site logo URL if set, empty string otherwise
 *
 * @param array $params Parameters passed to the function
 * @param object $smarty Smarty instance
 * @return string Logo URL or empty string
 */
function smarty_function_site_logo_url($params, &$smarty) {
    $logo = c('Garden.Logo');

    if (!$logo) {
        return '';
    }

    // Only trim slash from relative paths.
    if (!stringBeginsWith($logo, '//')) {
        $logo = ltrim($logo, '/');
    }

    // Fix the logo path.
    if (stringBeginsWith($logo, 'uploads/')) {
        $logo = substr($logo, strlen('uploads/'));
    }

    return Gdn_Upload::url($logo);
}
