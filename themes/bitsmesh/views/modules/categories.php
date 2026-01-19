<?php if (!defined('APPLICATION')) exit();
/**
 * BitsMesh Custom Categories Module View
 *
 * Displays category list with IconPark SVG icons.
 * Icons are stored in category's custom field 'IconID'.
 *
 * @copyright 2026 BitsMesh
 */

// Default icon mapping for categories without custom icons
$defaultIcons = [
    // English URL codes
    'daily' => 'tea',
    'tech' => 'formula',
    'info' => 'receiver',
    'test' => 'dashboard-one',
    'trade' => 'dollar',
    'carpool' => 'car',
    'promo' => 'hold-interface',
    'life' => 'oval-love-two',
    'dev' => 'terminal',
    'pics' => 'pic-one',
    'expose' => 'face-recognition',
    'internal' => 'open-one',
    'sandbox' => 'experiment',
    // Chinese URL codes (URL encoded)
    '交易' => 'dollar',
    '拼车' => 'car',
    '推广' => 'hold-interface',
    '日常' => 'tea',
    '技术' => 'formula',
    '情报' => 'receiver',
    '测评' => 'dashboard-one',
    '生活' => 'oval-love-two',
    '贴图' => 'pic-one',
    '曝光' => 'face-recognition',
    '内版' => 'open-one',
    '沙盒' => 'experiment',
];

/**
 * Get icon ID for a category
 *
 * @param object $category Category object
 * @param array $defaultIcons Default icon mapping
 * @return string Icon ID
 */
if (!function_exists('getCategoryIconId')) {
    function getCategoryIconId($category, $defaultIcons) {
        // Check for custom icon in category attributes
        $iconId = val('IconID', $category);
        if (!empty($iconId)) {
            return htmlspecialchars($iconId);
        }

        // Try URL code mapping (handle both raw and URL-encoded)
        $urlCode = val('UrlCode', $category, '');
        $urlCodeLower = strtolower($urlCode);
        $urlCodeDecoded = urldecode($urlCode);

        // Try exact match first
        if (isset($defaultIcons[$urlCodeLower])) {
            return $defaultIcons[$urlCodeLower];
        }

        // Try decoded match
        if (isset($defaultIcons[$urlCodeDecoded])) {
            return $defaultIcons[$urlCodeDecoded];
        }

        // Try category Name as fallback (for Chinese categories)
        $name = val('Name', $category, '');
        if (isset($defaultIcons[$name])) {
            return $defaultIcons[$name];
        }

        // Default icon
        return 'all-application';
    }
}

$CountDiscussions = 0;
$CategoryID = isset($this->_Sender->CategoryID) ? $this->_Sender->CategoryID : '';
$OnCategories = strtolower($this->_Sender->ControllerName) == 'categoriescontroller' && !is_numeric($CategoryID);

if ($this->Data !== FALSE) {
    foreach ($this->Data->result() as $Category) {
        $CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
    }
    ?>
    <div class="Box BoxCategories">
        <?php echo panelHeading(t('Categories')); ?>
        <ul class="PanelInfo PanelCategories">
            <?php
            // Category list without "All Categories" link (removed per design requirement)
            $MaxDepth = c('Vanilla.Categories.MaxDisplayDepth');

            foreach ($this->Data->result() as $Category) {
                if ($Category->CategoryID < 0 || $MaxDepth > 0 && $Category->Depth > $MaxDepth)
                    continue;

                $attributes = false;

                if ($Category->DisplayAs === 'Heading') {
                    $CssClass = 'Heading ' . $Category->CssClass;
                    $attributes = ['aria-level' => $Category->Depth + 2];
                } else {
                    $isActive = $CategoryID == $Category->CategoryID;
                    $CssClass = 'Depth' . $Category->Depth .
                        ($isActive ? ' Active current-category' : '') . ' ' .
                        $Category->CssClass;
                }

                if (is_array($attributes)) {
                    $attributes = attribute($attributes);
                }

                echo '<li class="ClearFix ' . $CssClass . '" ' . $attributes . '>';

                // Get icon for this category
                $iconId = getCategoryIconId($Category, $defaultIcons);
                $iconHtml = '<svg class="iconpark-icon"><use href="#' . $iconId . '"></use></svg>';

                if ($Category->DisplayAs === 'Heading') {
                    echo $iconHtml . ' <span>' . htmlspecialchars($Category->Name) . '</span>';
                } else {
                    echo anchor(
                        $iconHtml . ' <span>' . htmlspecialchars($Category->Name) . '</span>',
                        categoryUrl($Category),
                        'ItemLink'
                    );
                }
                echo "</li>\n";
            }
            ?>
        </ul>
    </div>
<?php
}
