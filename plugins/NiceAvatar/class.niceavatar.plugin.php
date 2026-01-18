<?php
/**
 * Nice Avatar Plugin
 *
 * Auto-generates beautiful avatars for users during registration.
 * Uses react-nice-avatar to render on client, then uploads as static image.
 *
 * Flow:
 * 1. User enters email on registration form
 * 2. JS generates avatar preview and base64 PNG
 * 3. On form submit, PHP saves the image as user's photo
 * 4. Avatar is now a static file - no runtime rendering needed
 *
 * @author BitsMesh
 * @license GPL-2.0-only
 */

class NiceAvatarPlugin extends Gdn_Plugin {

    /**
     * Plugin setup - runs on enable
     */
    public function setup() {
        // Nothing to set up
    }

    /**
     * Add settings link to dashboard menu
     *
     * @param Gdn_Controller $sender
     */
    public function base_getAppSettingsMenuItems_handler($sender) {
        $menu = &$sender->EventArguments['SideMenu'];
        $menu->addLink('Users', t('Nice Avatar'), 'settings/niceavatar', 'Garden.Settings.Manage');
    }

    /**
     * Load JS on registration page only
     *
     * @param EntryController $sender
     */
    public function entryController_render_before($sender) {
        if (!c('Plugins.NiceAvatar.Enabled', true)) {
            return;
        }

        // Only load on register page
        if (strtolower($sender->RequestMethod) === 'register') {
            $sender->addJsFile('nice-avatar.min.js', 'plugins/NiceAvatar');
            $sender->addCssFile('nice-avatar.css', 'plugins/NiceAvatar');
        }
    }

    /**
     * Also load on settings page for preview
     *
     * @param SettingsController $sender
     */
    public function settingsController_render_before($sender) {
        if (strtolower($sender->RequestMethod) === 'niceavatar') {
            $sender->addJsFile('nice-avatar.min.js', 'plugins/NiceAvatar');
            $sender->addCssFile('nice-avatar.css', 'plugins/NiceAvatar');
        }
    }

    /**
     * Settings page controller
     *
     * @param SettingsController $sender
     */
    public function settingsController_niceAvatar_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->setHighlightRoute('settings/niceavatar');
        $sender->title(t('Nice Avatar Settings'));

        // Handle form submission
        $configModel = new Gdn_ConfigurationModel($sender->Form);
        $configModel->setField([
            'Plugins.NiceAvatar.Enabled'
        ]);

        if ($sender->Form->authenticatedPostBack()) {
            $sender->Form->setFormValue('Plugins.NiceAvatar.Enabled', $sender->Form->getValue('Plugins_NiceAvatar_Enabled', true));

            if ($sender->Form->save() !== false) {
                $sender->informMessage(t('Your settings have been saved.'));
            }
        } else {
            $sender->Form->setData($configModel->Data);
        }

        // Check if JS bundle exists
        $jsPath = PATH_PLUGINS . '/NiceAvatar/js/nice-avatar.min.js';
        $sender->setData('JsBundleExists', file_exists($jsPath));

        $sender->render('settings', '', 'plugins/NiceAvatar');
    }

    /**
     * Hook: After user is inserted (new registration)
     * Process the avatar data submitted with the form
     *
     * @param UserModel $sender
     * @param array $args
     */
    public function userModel_afterInsertUser_handler($sender, $args) {
        if (!c('Plugins.NiceAvatar.Enabled', true)) {
            return;
        }

        $userID = val('InsertUserID', $sender->EventArguments);
        if (!$userID) {
            return;
        }

        // Check for avatar data in form submission
        $avatarData = val('NiceAvatarData', $_POST);
        if (!$avatarData || strpos($avatarData, 'data:image/png;base64,') !== 0) {
            return;
        }

        // Decode and save the avatar
        $this->saveAvatarFromBase64($userID, $avatarData);
    }

    /**
     * Save avatar from base64 data
     *
     * @param int $userID
     * @param string $base64Data
     * @return bool
     */
    private function saveAvatarFromBase64($userID, $base64Data) {
        try {
            // Remove data URL prefix
            $imageData = base64_decode(str_replace('data:image/png;base64,', '', $base64Data));
            if (!$imageData) {
                return false;
            }

            // Generate unique filename - Vanilla expects specific naming convention
            $hash = md5($userID . time());
            $baseFilename = "userpics/p{$hash}.png";
            $uploadPath = PATH_UPLOADS . '/' . $baseFilename;

            // Ensure directory exists
            $dir = dirname($uploadPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save the main image
            if (file_put_contents($uploadPath, $imageData) === false) {
                return false;
            }

            // Also save the 'n' (nano/thumbnail) version
            // Vanilla's changeBasename($photo, 'n%s') transforms 'p{hash}.png' to 'np{hash}.png'
            $nanoPath = PATH_UPLOADS . "/userpics/np{$hash}.png";
            copy($uploadPath, $nanoPath);

            // Update user's photo field (Vanilla expects the 'p' prefixed filename)
            Gdn::userModel()->setField($userID, 'Photo', $baseFilename);

            return true;

        } catch (Exception $e) {
            // Log error but don't break registration
            if (class_exists('Logger')) {
                Logger::log(Logger::ERROR, 'NiceAvatar', 'Failed to save avatar: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Bulk generate avatars for existing users (admin action)
     *
     * @param PluginController $sender
     */
    public function pluginController_niceAvatarBulkGenerate_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->deliveryType(DELIVERY_TYPE_VIEW);
        $sender->deliveryMethod(DELIVERY_METHOD_JSON);

        // This now requires client-side generation
        // Return instructions instead
        $sender->setData('Message', 'Bulk generation requires client-side processing. Please use the admin tool.');
        $sender->setData('UsersWithoutPhoto', Gdn::sql()
            ->select('COUNT(*)', '', 'Count')
            ->from('User')
            ->beginWhereGroup()
            ->where('Photo', '')
            ->orWhere('Photo IS NULL')
            ->endWhereGroup()
            ->get()
            ->firstRow()
            ->Count ?? 0
        );
        $sender->render('blank', 'utility', 'dashboard');
    }
}
