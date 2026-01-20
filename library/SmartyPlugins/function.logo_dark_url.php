<?php
/**
 * Smarty function to get dark mode logo URL
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 * @package vanilla-smarty
 */

/**
 * Returns the dark mode logo URL if set, empty string otherwise.
 * Falls back to default logo if dark mode logo is not set.
 *
 * @param array $params Parameters passed to the function
 *   - fallback: bool, if true returns default logo when dark logo not set (default: false)
 * @param object $smarty Smarty instance
 * @return string Logo URL or empty string
 */
function smarty_function_logo_dark_url($params, &$smarty) {
    $logo = c('Garden.LogoDark');

    // Fallback to default logo if dark logo not set and fallback requested
    if (!$logo && !empty($params['fallback'])) {
        $logo = c('Garden.Logo');
    }

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
