<?php
/**
 * BitsMesh Theme Hooks
 *
 * Handles theme-specific customizations including category icon selection.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

/**
 * Class BitsmeshThemeHooks
 */
class BitsmeshThemeHooks extends Gdn_Plugin {

    /**
     * Preset color schemes for quick selection.
     * @var array
     */
    private static $colorPresets = [
        'blue' => [
            'name' => 'Blue',
            'primary' => '#3B82F6',
            'secondary' => '#60A5FA',
            'darkPrimary' => '#2563EB',
            'darkSecondary' => '#3B82F6',
        ],
        'green' => [
            'name' => 'Green',
            'primary' => '#22C55E',
            'secondary' => '#4ADE80',
            'darkPrimary' => '#16A34A',
            'darkSecondary' => '#22C55E',
        ],
        'purple' => [
            'name' => 'Purple',
            'primary' => '#8B5CF6',
            'secondary' => '#A78BFA',
            'darkPrimary' => '#7C3AED',
            'darkSecondary' => '#8B5CF6',
        ],
        'orange' => [
            'name' => 'Orange',
            'primary' => '#F97316',
            'secondary' => '#FB923C',
            'darkPrimary' => '#EA580C',
            'darkSecondary' => '#F97316',
        ],
        'red' => [
            'name' => 'Red',
            'primary' => '#EF4444',
            'secondary' => '#F87171',
            'darkPrimary' => '#DC2626',
            'darkSecondary' => '#EF4444',
        ],
        'teal' => [
            'name' => 'Teal',
            'primary' => '#06B6D4',
            'secondary' => '#22D3EE',
            'darkPrimary' => '#0891B2',
            'darkSecondary' => '#06B6D4',
        ],
    ];

    /**
     * Available IconPark icons for category selection.
     * @var array
     */
    private static $availableIcons = [
        'all-application' => 'All Applications (Default)',
        'tea' => 'Tea (Daily)',
        'formula' => 'Formula (Tech)',
        'receiver' => 'Receiver (Info)',
        'dashboard-one' => 'Dashboard (Review)',
        'dollar' => 'Dollar (Trade)',
        'car' => 'Car (Carpool)',
        'hold-interface' => 'Hold Interface (Promo)',
        'oval-love-two' => 'Love (Life)',
        'terminal' => 'Terminal (Dev)',
        'pic-one' => 'Picture (Pics)',
        'face-recognition' => 'Face (Expose)',
        'open-one' => 'Open (Internal)',
        'experiment' => 'Experiment (Sandbox)',
    ];

    /**
     * Run once on theme enable.
     * Adds IconID column to Category table if it doesn't exist.
     *
     * @return void
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Database structure modifications.
     *
     * @return void
     */
    public function structure() {
        $database = Gdn::database();
        $construct = $database->structure();

        // Add IconID column to Category table
        $construct->table('Category');
        if (!$construct->columnExists('IconID')) {
            $construct->column('IconID', 'varchar(50)', true);
            $construct->set(false, false);
        }
    }

    /**
     * Register theme settings menu item in dashboard.
     *
     * @param object $sender The controller instance.
     * @return void
     */
    public function base_getAppSettingsMenuItems_handler($sender) {
        $menu = &$sender->EventArguments['SideMenu'];
        $menu->addLink('Appearance', t('BitsMesh Theme'), 'settings/bitsmesh', 'Garden.Settings.Manage', ['class' => 'nav-bitsmesh']);
    }

    /**
     * Theme settings page controller.
     *
     * @param SettingsController $sender The controller instance.
     * @return void
     */
    public function settingsController_bitsmesh_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->setHighlightRoute('settings/bitsmesh');
        $sender->title(t('BitsMesh Theme Settings'));
        $sender->addSideMenu('settings/bitsmesh');

        // Add JS and CSS for color picker
        $sender->addJsFile('color-settings.js', 'themes/bitsmesh');
        $sender->addCssFile('settings.css', 'themes/bitsmesh');

        // Field mappings: form field name (underscore) => config key (dot)
        $fieldMappings = [
            'Themes_BitsMesh_PrimaryColor' => 'Themes.BitsMesh.PrimaryColor',
            'Themes_BitsMesh_SecondaryColor' => 'Themes.BitsMesh.SecondaryColor',
            'Themes_BitsMesh_DarkPrimaryColor' => 'Themes.BitsMesh.DarkPrimaryColor',
            'Themes_BitsMesh_DarkSecondaryColor' => 'Themes.BitsMesh.DarkSecondaryColor',
            'Themes_BitsMesh_GridEnabled' => 'Themes.BitsMesh.GridEnabled',
            'Themes_BitsMesh_GridColor' => 'Themes.BitsMesh.GridColor',
            'Themes_BitsMesh_DarkGridColor' => 'Themes.BitsMesh.DarkGridColor',
            'Themes_BitsMesh_GridBgColor' => 'Themes.BitsMesh.GridBgColor',
            'Themes_BitsMesh_DarkGridBgColor' => 'Themes.BitsMesh.DarkGridBgColor',
        ];

        // Handle form submission
        if ($sender->Form->authenticatedPostBack()) {
            // Validate color inputs using form field names
            $colorFields = [
                'Themes_BitsMesh_PrimaryColor',
                'Themes_BitsMesh_SecondaryColor',
                'Themes_BitsMesh_DarkPrimaryColor',
                'Themes_BitsMesh_DarkSecondaryColor',
                'Themes_BitsMesh_GridColor',
                'Themes_BitsMesh_DarkGridColor',
                'Themes_BitsMesh_GridBgColor',
                'Themes_BitsMesh_DarkGridBgColor',
            ];

            foreach ($colorFields as $field) {
                $value = $sender->Form->getFormValue($field);
                if (!empty($value) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                    $sender->Form->addError(sprintf(t('%s must be a valid hex color (e.g., #3B82F6).'), $field));
                }
            }

            if ($sender->Form->errorCount() === 0) {
                // Save each field directly using saveToConfig()
                $configValues = [];
                foreach ($fieldMappings as $formField => $configKey) {
                    $value = $sender->Form->getFormValue($formField);

                    // Handle checkbox (GridEnabled)
                    if ($formField === 'Themes_BitsMesh_GridEnabled') {
                        $value = $value ? true : false;
                    }

                    // Only save non-empty values (or false for checkbox)
                    if ($value !== null && $value !== '') {
                        $configValues[$configKey] = $value;
                    }
                }

                // Save all config values at once
                saveToConfig($configValues);

                $sender->informMessage(t('Your settings have been saved.'));
            }
        }

        // Load existing config values into form (using underscore field names)
        $formData = [];
        foreach ($fieldMappings as $formField => $configKey) {
            $defaultValue = self::getDefaultValueForField($configKey);
            $formData[$formField] = c($configKey, $defaultValue);
        }
        $sender->Form->setData($formData);

        // Pass data to view
        $sender->setData('ColorPresets', self::$colorPresets);
        $sender->setData('DefaultColors', self::getDefaultColors());

        $sender->render('bitsmesh-settings', '', 'themes/bitsmesh');
    }

    /**
     * Get default value for a config field.
     *
     * @param string $configKey The config key.
     * @return mixed Default value.
     */
    private static function getDefaultValueForField($configKey) {
        $defaults = [
            'Themes.BitsMesh.PrimaryColor' => '#3B82F6',
            'Themes.BitsMesh.SecondaryColor' => '#60A5FA',
            'Themes.BitsMesh.DarkPrimaryColor' => '#2563EB',
            'Themes.BitsMesh.DarkSecondaryColor' => '#3B82F6',
            'Themes.BitsMesh.GridEnabled' => true,
            'Themes.BitsMesh.GridColor' => '#d4d4d4',
            'Themes.BitsMesh.DarkGridColor' => '#404040',
            'Themes.BitsMesh.GridBgColor' => '#fffcf8',
            'Themes.BitsMesh.DarkGridBgColor' => '#1a1a1a',
        ];
        return isset($defaults[$configKey]) ? $defaults[$configKey] : '';
    }

    /**
     * Get theme colors from configuration.
     *
     * @return array Theme colors.
     */
    private static function getThemeColors() {
        return [
            'primary' => c('Themes.BitsMesh.PrimaryColor', '#3B82F6'),
            'secondary' => c('Themes.BitsMesh.SecondaryColor', '#60A5FA'),
            'darkPrimary' => c('Themes.BitsMesh.DarkPrimaryColor', '#2563EB'),
            'darkSecondary' => c('Themes.BitsMesh.DarkSecondaryColor', '#3B82F6'),
            'grid' => c('Themes.BitsMesh.GridColor', '#d4d4d4'),
            'darkGrid' => c('Themes.BitsMesh.DarkGridColor', '#404040'),
            'gridBg' => c('Themes.BitsMesh.GridBgColor', '#fffcf8'),
            'darkGridBg' => c('Themes.BitsMesh.DarkGridBgColor', '#1a1a1a'),
        ];
    }

    /**
     * Get default color values.
     *
     * @return array Default colors.
     */
    private static function getDefaultColors() {
        return [
            'primaryColor' => '#3B82F6',
            'secondaryColor' => '#60A5FA',
            'darkPrimaryColor' => '#2563EB',
            'darkSecondaryColor' => '#3B82F6',
            'gridEnabled' => true,
            'gridColor' => '#d4d4d4',
            'darkGridColor' => '#404040',
            'gridBgColor' => '#fffcf8',
            'darkGridBgColor' => '#1a1a1a',
        ];
    }

    /**
     * Inject dynamic theme styles into page head.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectThemeStyles($sender) {
        $colors = self::getThemeColors();
        $gridEnabled = c('Themes.BitsMesh.GridEnabled', true);

        // Sanitize color values for CSS injection
        foreach ($colors as $key => $value) {
            $colors[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        $css = '<style id="bitsmesh-dynamic-vars">
:root {
    --bits-primary: ' . $colors['primary'] . ';
    --bits-secondary: ' . $colors['secondary'] . ';
}
body.dark-layout {
    --bits-primary: ' . $colors['darkPrimary'] . ';
    --bits-secondary: ' . $colors['darkSecondary'] . ';
}';

        // Grid background styles
        if ($gridEnabled) {
            $css .= '
@media only screen and (min-width: 500px) {
    body {
        background-color: ' . $colors['gridBg'] . ';
        background-image: linear-gradient(' . $colors['grid'] . ' 1px, transparent 0),
                          linear-gradient(90deg, ' . $colors['grid'] . ' 1px, transparent 0);
        background-size: 32px 32px;
    }
    body.dark-layout {
        background-color: ' . $colors['darkGridBg'] . ';
        background-image: linear-gradient(' . $colors['darkGrid'] . ' 1px, transparent 1px),
                          linear-gradient(to right, ' . $colors['darkGrid'] . ' 1px, transparent 1px);
        background-size: 32px 32px;
    }
}';
        }

        $css .= '</style>';

        if ($sender->Head) {
            $sender->Head->addString($css);
        }
    }

    /**
     * Inject sidebar data for Smarty templates.
     *
     * Sets data that will be available in sidebar-welcome.tpl:
     * - SidebarIsLoggedIn: boolean
     * - SidebarSiteTitle: string
     * - SidebarSiteDescription: string
     * - SidebarUserCount: int
     * - SidebarUserDiscussionCount: int
     * - SidebarUserCommentCount: int
     * - SidebarUserName: string
     * - SidebarUserPhoto: string
     * - SidebarUserProfileUrl: string
     * - SidebarSignInUrl: string
     * - SidebarRegisterUrl: string
     * - SidebarNewMembers: array
     * - SidebarCategoryName: string (category pages only)
     * - SidebarCategoryDescription: string (category pages only)
     * - isCategoryPage: boolean
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectSidebarData($sender) {
        $session = Gdn::session();
        $isLoggedIn = $session->isValid();

        // Site info
        $siteTitle = c('Garden.HomepageTitle', c('Garden.Title', 'Welcome'));
        $siteDescription = c('Garden.Description', '');

        // User statistics
        $userCount = 0;
        try {
            $userModel = new UserModel();
            $userCount = $userModel->getCountLike();
        } catch (Exception $e) {
            // Silently fail - stats panel will show 0
        }

        // User's discussion count and comment count (for logged in users)
        $userDiscussionCount = 0;
        $userCommentCount = 0;
        $userName = '';
        $userPhoto = '';
        $userProfileUrl = '';

        if ($isLoggedIn && $session->User) {
            $userDiscussionCount = val('CountDiscussions', $session->User, 0);
            $userCommentCount = val('CountComments', $session->User, 0);
            $userName = val('Name', $session->User, '');
            $userProfileUrl = userUrl($session->User);

            // Get user photo URL
            $photo = val('Photo', $session->User, '');
            if ($photo) {
                if (!isUrl($photo)) {
                    $photo = Gdn_Upload::url(changeBasename($photo, 'n%s'));
                }
            } else {
                // Use default avatar
                $photo = UserModel::getDefaultAvatarUrl($session->User);
            }
            $userPhoto = $photo;
        }

        // Get newest members (limit to 8)
        $newMembers = [];
        try {
            $userModel = new UserModel();
            $newMembersData = $userModel->getWhere(
                ['Deleted' => 0, 'Banned' => 0],
                'DateInserted',
                'desc',
                8
            );
            if ($newMembersData) {
                foreach ($newMembersData as $member) {
                    $memberPhoto = val('Photo', $member, '');
                    if ($memberPhoto && !isUrl($memberPhoto)) {
                        $memberPhoto = Gdn_Upload::url(changeBasename($memberPhoto, 'n%s'));
                    } elseif (!$memberPhoto) {
                        $memberPhoto = UserModel::getDefaultAvatarUrl($member);
                    }

                    $newMembers[] = [
                        'Name' => val('Name', $member, ''),
                        'Photo' => $memberPhoto,
                        'Url' => userUrl($member),
                    ];
                }
            }
        } catch (Exception $e) {
            // Silently fail - new members list will be empty
        }

        // Set all sidebar data
        $sender->setData('SidebarIsLoggedIn', $isLoggedIn);
        $sender->setData('SidebarSiteTitle', $siteTitle);
        $sender->setData('SidebarSiteDescription', $siteDescription);
        $sender->setData('SidebarUserCount', $userCount);
        $sender->setData('SidebarUserDiscussionCount', $userDiscussionCount);
        $sender->setData('SidebarUserCommentCount', $userCommentCount);
        $sender->setData('SidebarUserName', $userName);
        $sender->setData('SidebarUserPhoto', $userPhoto);
        $sender->setData('SidebarUserProfileUrl', $userProfileUrl);
        $sender->setData('SidebarNewMembers', $newMembers);
        $sender->setData('SidebarSignInUrl', url('/entry/signin'));
        $sender->setData('SidebarRegisterUrl', url('/entry/register'));
        $sender->setData('SidebarSignOutUrl', url(signOutUrl()));
        $sender->setData('SidebarNewDiscussionUrl', url('/post/discussion'));
        $sender->setData('SidebarCategoriesUrl', url('/categories'));
        $sender->setData('SidebarDiscussionsUrl', url('/discussions'));
        $sender->setData('SidebarActivityUrl', url('/activity'));
        $sender->setData('SidebarMyDiscussionsUrl', url('/discussions/mine'));
        $sender->setData('SidebarBookmarksUrl', url('/discussions/bookmarked'));
        $sender->setData('SidebarSettingsUrl', url('/profile/preferences'));

        // Category page detection and data injection
        // Check if we're on a category page (CategoriesController with a specific category)
        $isCategoryPage = false;
        $categoryName = '';
        $categoryDescription = '';

        $controllerName = strtolower(get_class($sender));
        if (strpos($controllerName, 'categoriescontroller') !== false) {
            // Get category data from the controller
            $category = $sender->data('Category');
            if ($category && is_object($category)) {
                $isCategoryPage = true;
                $categoryName = val('Name', $category, '');
                $categoryDescription = val('Description', $category, '');
            } elseif ($category && is_array($category)) {
                $isCategoryPage = true;
                $categoryName = isset($category['Name']) ? $category['Name'] : '';
                $categoryDescription = isset($category['Description']) ? $category['Description'] : '';
            }
        }

        $sender->setData('isCategoryPage', $isCategoryPage);
        $sender->setData('SidebarCategoryName', $categoryName);
        $sender->setData('SidebarCategoryDescription', $categoryDescription);
    }

    /**
     * Add icon picker to category add/edit form.
     *
     * @param SettingsController $sender The controller instance.
     * @param array $args Event arguments.
     * @return void
     */
    public function settingsController_addEditCategory_handler($sender, $args) {
        // Add JS and CSS for icon picker
        $sender->addJsFile('icon-picker.js', 'themes/bitsmesh');
        $sender->addCssFile('icon-picker.css', 'themes/bitsmesh');

        // Get current icon value
        $category = val('Category', $sender, null);
        $currentIcon = '';
        if ($category) {
            $currentIcon = val('IconID', $category, '');
        }

        // Set available icons for the view
        $sender->setData('AvailableIcons', self::$availableIcons);
        $sender->setData('CurrentIcon', $currentIcon);

        // Add the IconID field to extended fields
        if (!isset($sender->Data['_ExtendedFields'])) {
            $sender->Data['_ExtendedFields'] = [];
        }

        // Create custom icon picker HTML
        $iconPickerHtml = $this->getIconPickerHtml($currentIcon);
        $sender->setData('IconPickerHtml', $iconPickerHtml);
    }

    /**
     * Generate icon picker HTML.
     *
     * @param string $currentIcon Current selected icon ID.
     * @return string HTML for icon picker.
     */
    private function getIconPickerHtml($currentIcon) {
        $html = '<li class="form-group" id="IconPickerGroup">';
        $html .= '<div class="label-wrap"><label for="Form_IconID">' . t('Category Icon') . '</label></div>';
        $html .= '<div class="input-wrap">';
        $html .= '<div class="bits-icon-picker">';
        $html .= '<input type="hidden" name="IconID" id="Form_IconID" value="' . htmlspecialchars($currentIcon) . '">';

        // Current selection preview
        $html .= '<div class="bits-icon-preview">';
        $html .= '<svg class="iconpark-icon" id="IconPreview" width="24" height="24"><use href="#' . ($currentIcon ?: 'all-application') . '"></use></svg>';
        $html .= '<span class="bits-icon-name" id="IconName">' . htmlspecialchars(self::$availableIcons[$currentIcon] ?? 'Select an icon') . '</span>';
        $html .= '<button type="button" class="btn btn-secondary bits-icon-toggle" id="IconPickerToggle">' . t('Change') . '</button>';
        $html .= '</div>';

        // Icon grid (hidden by default)
        $html .= '<div class="bits-icon-grid" id="IconGrid" style="display:none">';
        foreach (self::$availableIcons as $iconId => $iconName) {
            $selectedClass = ($iconId === $currentIcon) ? ' selected' : '';
            $html .= '<button type="button" class="bits-icon-option' . $selectedClass . '" data-icon="' . htmlspecialchars($iconId) . '" title="' . htmlspecialchars($iconName) . '">';
            $html .= '<svg class="iconpark-icon" width="20" height="20"><use href="#' . htmlspecialchars($iconId) . '"></use></svg>';
            $html .= '</button>';
        }
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    /**
     * Inject icon picker into edit category form after category settings.
     * The Twig template fires this event without fireAs(), so use VanillaSettingsController prefix.
     *
     * @param object $sender The pluggable object.
     * @param array $args Event arguments.
     * @return void
     */
    public function vanillaSettingsController_afterCategorySettings_handler($sender, $args) {
        // Get the controller
        $controller = Gdn::controller();
        if (!$controller) {
            return;
        }

        // Output the icon picker HTML
        $iconPickerHtml = val('IconPickerHtml', $controller->Data, '');
        if ($iconPickerHtml) {
            echo $iconPickerHtml;
        }
    }

    /**
     * Save IconID when category is saved.
     *
     * @param CategoryModel $sender The CategoryModel instance.
     * @param array $args Event arguments.
     * @return void
     */
    public function categoryModel_beforeSaveCategory_handler($sender, $args) {
        $formPostValues = &$args['FormPostValues'];

        // Validate and sanitize IconID
        if (isset($formPostValues['IconID'])) {
            $iconId = trim($formPostValues['IconID']);

            // Validate icon ID against whitelist
            if (!empty($iconId) && !array_key_exists($iconId, self::$availableIcons)) {
                $iconId = ''; // Reset to empty if invalid
            }

            $formPostValues['IconID'] = $iconId;
        }
    }

    /**
     * Render before hook - inject dynamic styles and resources.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    public function base_render_before($sender) {
        // Frontend: inject dynamic theme styles and load JS files
        if (!inSection('Dashboard')) {
            $this->injectThemeStyles($sender);
            $this->injectSidebarData($sender);
            $this->injectPostListControlerData($sender);

            // Load theme JavaScript files
            $sender->addJsFile('darkMode.js', 'themes/bitsmesh');
            $sender->addJsFile('theme.js', 'themes/bitsmesh');

            return;
        }

        // Dashboard: handle specific pages
        $controllerName = strtolower($sender->ControllerName);
        $method = strtolower(val('RequestMethod', $sender, ''));

        // Category add/edit pages: include SVG sprite
        if ($controllerName === 'vanillasettingscontroller' &&
            ($method === 'addcategory' || $method === 'editcategory')) {

            // Include SVG sprite for icon preview
            $spritePath = PATH_THEMES . '/bitsmesh/views/iconpark-sprite.tpl';
            if (file_exists($spritePath)) {
                $sender->addAsset('Content', $this->getSvgSpriteHtml(), 'IconParkSprite');
            }
        }
    }

    /**
     * Get SVG sprite HTML for dashboard.
     *
     * @return string SVG sprite HTML.
     */
    private function getSvgSpriteHtml() {
        $spritePath = PATH_THEMES . '/bitsmesh/views/iconpark-sprite.tpl';
        if (!file_exists($spritePath)) {
            return '';
        }

        // Read the sprite file and extract the SVG content
        $content = file_get_contents($spritePath);

        // Remove Smarty comments
        $content = preg_replace('/\{\*.*?\*\}/s', '', $content);

        return $content;
    }

    /**
     * Get available icons list.
     *
     * @return array Available icons.
     */
    public static function getAvailableIcons() {
        return self::$availableIcons;
    }

    /**
     * Inject post list controller data for Smarty templates.
     *
     * @param Gdn_Controller $sender The controller instance.
     * @return void
     */
    private function injectPostListControlerData($sender) {
        // Default values - always set to prevent template errors
        $sender->setData('BitsShowPostListControler', false);
        $sender->setData('BitsCurrentSort', 'posts');
        $sender->setData('BitsSortCommentsUrl', '');
        $sender->setData('BitsSortPostsUrl', '');
        $sender->setData('BitsCurrentPage', 1);
        $sender->setData('BitsTotalPages', 1);
        $sender->setData('BitsShowPager', false);
        $sender->setData('BitsPagerPages', []);

        // Only inject on discussion list pages
        $controllerName = strtolower(get_class($sender));
        $isDiscussionsList = (
            strpos($controllerName, 'discussionscontroller') !== false ||
            strpos($controllerName, 'categoriescontroller') !== false
        );

        if (!$isDiscussionsList) {
            return;
        }

        // Get current path for building URLs
        $request = Gdn::request();
        $currentPath = $request->path();
        if (empty($currentPath)) {
            $currentPath = 'discussions';
        }

        // Clean page numbers from path
        $basePath = preg_replace('#/p\d+/?$#i', '', $currentPath);
        if (empty($basePath)) {
            $basePath = 'discussions';
        }

        // Generate sort URLs using custom sortBy parameter
        // sortBy=replyTime (新评论 - sort by latest reply time)
        // sortBy=postTime (新帖子 - sort by post creation time)
        // DiscussionModel::getSortFromArray() maps these to internal sort keys
        $sortCommentsUrl = url($basePath . '?sortBy=replyTime');
        $sortPostsUrl = url($basePath . '?sortBy=postTime');

        // Determine current sort from sortBy parameter
        // Default to 'comments' (replyTime/hot - by latest reply)
        $currentSort = 'comments';
        $sortByParam = $request->get('sortBy', '');
        if ($sortByParam === 'postTime') {
            $currentSort = 'posts';
        }

        // Set template data
        $sender->setData('BitsShowPostListControler', true);
        $sender->setData('BitsCurrentSort', $currentSort);
        $sender->setData('BitsSortCommentsUrl', $sortCommentsUrl);
        $sender->setData('BitsSortPostsUrl', $sortPostsUrl);

        // Get pager data from PagerModule
        // Pass sortBy value to ensure it's preserved in pager URLs
        $sortByValue = ($currentSort === 'posts') ? 'postTime' : 'replyTime';
        $this->injectPagerData($sender, $basePath, $sortByValue);
    }

    /**
     * Inject pager data for Smarty templates.
     *
     * Calculates page numbers to display and generates URLs.
     * Uses modern forum style pagination: 1 2 3 4 5 .. 100
     *
     * @param Gdn_Controller $sender The controller instance.
     * @param string $basePath The base URL path for building pager links.
     * @param string $sortBy The sortBy parameter value (postTime or replyTime).
     * @return void
     */
    private function injectPagerData($sender, $basePath, $sortBy = 'replyTime') {
        // Try to get pager from controller data first
        $pager = PagerModule::current();

        // Fallback to controller's pager data
        $offset = 0;
        $limit = c('Vanilla.Discussions.PerPage', 30);
        $totalRecords = 0;

        if ($pager && is_object($pager)) {
            $offset = isset($pager->Offset) ? (int)$pager->Offset : 0;
            $limit = isset($pager->Limit) && $pager->Limit > 0 ? (int)$pager->Limit : $limit;
            $totalRecords = isset($pager->TotalRecords) ? (int)$pager->TotalRecords : 0;
        }

        // Also check controller data for fallback
        if ($totalRecords <= 0) {
            $totalRecords = (int)$sender->data('RecordCount', 0);
        }
        if ($totalRecords <= 0) {
            $totalRecords = (int)$sender->data('CountDiscussions', 0);
        }

        // Last resort: query DiscussionModel for count
        if ($totalRecords <= 0) {
            try {
                $discussionModel = new DiscussionModel();
                $totalRecords = $discussionModel->getCount();
            } catch (Exception $e) {
                // Silently fail
            }
        }

        // Get current page from URL
        $request = Gdn::request();
        $path = $request->path();
        if (preg_match('#/p(\d+)/?$#i', $path, $matches)) {
            $currentPage = (int)$matches[1];
        } else {
            $currentPage = 1;
        }

        // Calculate total pages
        $totalPages = $limit > 0 && $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;

        // Ensure valid values
        $currentPage = max(1, min($currentPage, $totalPages));
        $totalPages = max(1, $totalPages);

        // Only show pager if there are multiple pages
        if ($totalPages <= 1) {
            $sender->setData('BitsShowPager', false);
            return;
        }

        // Build page URLs with sortBy parameter
        // Always include sortBy to maintain sort state across pagination
        $queryString = '?sortBy=' . urlencode($sortBy);

        // Build array of pages to display (modern forum style)
        $pages = $this->buildPagerPages($currentPage, $totalPages, $basePath, $queryString);

        // Build prev/next URLs
        $prevUrl = $currentPage > 1 ? $this->buildPageUrl($basePath, $currentPage - 1, $queryString) : '';
        $nextUrl = $currentPage < $totalPages ? $this->buildPageUrl($basePath, $currentPage + 1, $queryString) : '';

        // Set pager data
        $sender->setData('BitsShowPager', true);
        $sender->setData('BitsCurrentPage', $currentPage);
        $sender->setData('BitsTotalPages', $totalPages);
        $sender->setData('BitsPagerPages', $pages);
        $sender->setData('BitsPagerPrevUrl', $prevUrl);
        $sender->setData('BitsPagerNextUrl', $nextUrl);
    }

    /**
     * Build array of page info for the pager template.
     *
     * Returns an array like:
     * [
     *   ['page' => 1, 'url' => '...', 'current' => true],
     *   ['page' => 2, 'url' => '...', 'current' => false],
     *   ['page' => '..', 'url' => '', 'ellipsis' => true],
     *   ['page' => 100, 'url' => '...', 'current' => false],
     * ]
     *
     * @param int $currentPage Current page number.
     * @param int $totalPages Total number of pages.
     * @param string $basePath Base URL path.
     * @param string $queryString Query string parameters.
     * @return array Array of page info.
     */
    private function buildPagerPages($currentPage, $totalPages, $basePath, $queryString) {
        $pages = [];
        $range = 2; // Show 2 pages on each side of current

        // Simple case: show all pages if 7 or fewer
        if ($totalPages <= 7) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'page' => $i,
                    'url' => $this->buildPageUrl($basePath, $i, $queryString),
                    'current' => ($i === $currentPage),
                    'ellipsis' => false,
                ];
            }
            return $pages;
        }

        // Complex case: show pages with ellipsis
        $showStart = max(1, $currentPage - $range);
        $showEnd = min($totalPages, $currentPage + $range);

        // Adjust range to always show 5 consecutive pages when possible
        if ($showEnd - $showStart < 4) {
            if ($showStart === 1) {
                $showEnd = min($totalPages, 5);
            } elseif ($showEnd === $totalPages) {
                $showStart = max(1, $totalPages - 4);
            }
        }

        // Add first page if not in range
        if ($showStart > 1) {
            $pages[] = [
                'page' => 1,
                'url' => $this->buildPageUrl($basePath, 1, $queryString),
                'current' => (1 === $currentPage),
                'ellipsis' => false,
            ];

            // Add ellipsis if there's a gap
            if ($showStart > 2) {
                $pages[] = [
                    'page' => '..',
                    'url' => '',
                    'current' => false,
                    'ellipsis' => true,
                ];
            }
        }

        // Add pages in range
        for ($i = $showStart; $i <= $showEnd; $i++) {
            $pages[] = [
                'page' => $i,
                'url' => $this->buildPageUrl($basePath, $i, $queryString),
                'current' => ($i === $currentPage),
                'ellipsis' => false,
            ];
        }

        // Add last page if not in range
        if ($showEnd < $totalPages) {
            // Add ellipsis if there's a gap
            if ($showEnd < $totalPages - 1) {
                $pages[] = [
                    'page' => '..',
                    'url' => '',
                    'current' => false,
                    'ellipsis' => true,
                ];
            }

            $pages[] = [
                'page' => $totalPages,
                'url' => $this->buildPageUrl($basePath, $totalPages, $queryString),
                'current' => ($totalPages === $currentPage),
                'ellipsis' => false,
            ];
        }

        return $pages;
    }

    /**
     * Build URL for a specific page number.
     *
     * @param string $basePath Base URL path.
     * @param int $page Page number.
     * @param string $queryString Query string parameters.
     * @return string Full URL for the page.
     */
    private function buildPageUrl($basePath, $page, $queryString) {
        // Page 1 doesn't need 'p1' in URL
        if ($page <= 1) {
            return url($basePath . $queryString);
        }
        return url($basePath . '/p' . $page . $queryString);
    }
}
