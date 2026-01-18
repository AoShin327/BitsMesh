<?php
if (!defined('APPLICATION')) {
    exit();
}

/**
 * BitsMesh Theme Configuration
 *
 * This file defines the theme options available in the dashboard.
 */

// Theme color options
$Configuration['Garden']['ThemeOptions']['Options']['PrimaryColor'] = [
    'Type' => 'color',
    'Default' => '#2ea44f',
    'Description' => 'Primary theme color (buttons, links, accents)'
];

$Configuration['Garden']['ThemeOptions']['Options']['SecondaryColor'] = [
    'Type' => 'color',
    'Default' => '#45ca6b',
    'Description' => 'Secondary theme color (hover states)'
];

$Configuration['Garden']['ThemeOptions']['Options']['TextColor'] = [
    'Type' => 'color',
    'Default' => '#333333',
    'Description' => 'Main text color'
];

$Configuration['Garden']['ThemeOptions']['Options']['LinkColor'] = [
    'Type' => 'color',
    'Default' => '#555555',
    'Description' => 'Link color'
];

$Configuration['Garden']['ThemeOptions']['Options']['BgMainColor'] = [
    'Type' => 'color',
    'Default' => '#ffffff',
    'Description' => 'Main background color'
];

$Configuration['Garden']['ThemeOptions']['Options']['BgSubColor'] = [
    'Type' => 'color',
    'Default' => '#fbfbfb',
    'Description' => 'Secondary background color'
];

// Dark mode defaults
$Configuration['Garden']['ThemeOptions']['Options']['DarkTextColor'] = [
    'Type' => 'color',
    'Default' => '#aaaaaa',
    'Description' => 'Dark mode text color'
];

$Configuration['Garden']['ThemeOptions']['Options']['DarkBgMainColor'] = [
    'Type' => 'color',
    'Default' => '#272727',
    'Description' => 'Dark mode main background'
];

$Configuration['Garden']['ThemeOptions']['Options']['DarkBgSubColor'] = [
    'Type' => 'color',
    'Default' => '#3b3b3b',
    'Description' => 'Dark mode secondary background'
];
