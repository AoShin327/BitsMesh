<?php
/**
 * InviteCode Model
 *
 * Handles invitation code system for BitsMesh theme.
 * Users can spend credits to generate invite codes.
 * Admins can generate codes without spending credits.
 *
 * @package BitsMesh
 * @since 1.0
 */

/**
 * InviteCodeModel - Manages invitation codes.
 *
 * Database Tables:
 * - GDN_InviteCode: Stores invitation codes
 *
 * User Table Fields:
 * - InviteCodeID: The invite code used to register
 * - InvitedByUserID: The user who invited this user
 */
class InviteCodeModel extends Gdn_Model {

    /** @var InviteCodeModel Singleton instance */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('InviteCode');
        $this->PrimaryKey = 'InviteCodeID';
    }

    /**
     * Get singleton instance.
     *
     * @return InviteCodeModel
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new InviteCodeModel();
        }
        return self::$instance;
    }

    /**
     * Get configuration value for credit cost.
     *
     * @return int
     */
    public static function getCreditCost() {
        return (int)c('BitsMesh.Invite.CreditCost', 1000);
    }

    /**
     * Get configuration value for default max uses.
     *
     * @return int
     */
    public static function getDefaultMaxUses() {
        return (int)c('BitsMesh.Invite.DefaultMaxUses', 1);
    }

    /**
     * Get configuration value for default expiry days.
     *
     * @return int
     */
    public static function getDefaultExpiryDays() {
        return (int)c('BitsMesh.Invite.DefaultExpiryDays', 30);
    }

    /**
     * Get configuration value for inviter bonus.
     *
     * @return int
     */
    public static function getInviterBonus() {
        return (int)c('BitsMesh.Invite.InviterBonus', 0);
    }

    /**
     * Generate a unique invite code.
     *
     * @return string 8-character alphanumeric code
     */
    public static function generateCode() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing chars: I,O,0,1
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Create an invite code for a user (costs credits).
     *
     * @param int $userID User creating the code
     * @return array ['success', 'code', 'error', 'message']
     */
    public function createUserCode($userID) {
        $userID = (int)$userID;

        if (!$userID) {
            return ['success' => false, 'error' => 'NotLoggedIn', 'message' => t('Please sign in first.', '请先登录')];
        }

        // Get cost from config
        $creditCost = self::getCreditCost();

        // Check user's credit balance
        $user = Gdn::userModel()->getID($userID);
        $currentBalance = (int)val('Points', $user, 0);

        if ($currentBalance < $creditCost) {
            return [
                'success' => false,
                'error' => 'InsufficientCredits',
                'message' => sprintf(t('Insufficient credits. You need %d chicken legs.', '鸡腿不足，需要 %d 个鸡腿'), $creditCost)
            ];
        }

        // Generate unique code
        $code = $this->generateUniqueCode();

        // Get default settings from config
        $maxUses = self::getDefaultMaxUses();
        $expiryDays = self::getDefaultExpiryDays();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));

        // Deduct credits
        $newBalance = $currentBalance - $creditCost;
        Gdn::userModel()->setField($userID, 'Points', $newBalance);

        // Log credit transaction
        Gdn::sql()->insert('CreditLog', [
            'UserID' => $userID,
            'Amount' => -$creditCost,
            'Balance' => $newBalance,
            'Type' => 'invite_create',
            'RelatedID' => null,
            'RelatedUserID' => null,
            'Note' => sprintf(t('Generated invite code: %s', '生成邀请码: %s'), $code),
            'DateInserted' => Gdn_Format::toDateTime()
        ]);

        // Insert invite code
        $this->SQL->insert('InviteCode', [
            'Code' => $code,
            'CreatorUserID' => $userID,
            'MaxUses' => $maxUses,
            'UseCount' => 0,
            'ExpiresAt' => $expiresAt,
            'CreditCost' => $creditCost,
            'DateInserted' => Gdn_Format::toDateTime(),
            'IsActive' => 1
        ]);

        return [
            'success' => true,
            'code' => $code,
            'expiresAt' => $expiresAt,
            'maxUses' => $maxUses,
            'newBalance' => $newBalance,
            'message' => sprintf(t('Invite code generated: %s', '邀请码已生成: %s'), $code)
        ];
    }

    /**
     * Create an invite code by admin (no credit cost).
     *
     * @param int $maxUses Maximum uses for this code
     * @param int|null $expiryDays Days until expiry (null = never expires)
     * @param int $count Number of codes to generate
     * @return array ['success', 'codes', 'error', 'message']
     */
    public function createAdminCode($maxUses = 1, $expiryDays = null, $count = 1) {
        $codes = [];
        $count = max(1, min(100, (int)$count)); // Limit to 1-100

        $expiresAt = null;
        if ($expiryDays !== null && $expiryDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));
        }

        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateUniqueCode();

            $this->SQL->insert('InviteCode', [
                'Code' => $code,
                'CreatorUserID' => 0, // 0 = admin
                'MaxUses' => $maxUses,
                'UseCount' => 0,
                'ExpiresAt' => $expiresAt,
                'CreditCost' => 0,
                'DateInserted' => Gdn_Format::toDateTime(),
                'IsActive' => 1
            ]);

            $codes[] = $code;
        }

        return [
            'success' => true,
            'codes' => $codes,
            'message' => sprintf(t('Generated %d invite code(s)', '已生成 %d 个邀请码'), count($codes))
        ];
    }

    /**
     * Generate a unique code (checks for collision).
     *
     * @return string
     */
    protected function generateUniqueCode() {
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = self::generateCode();
            $existing = $this->getByCode($code);
            if (!$existing) {
                return $code;
            }
        }
        // Fallback: add timestamp suffix
        return self::generateCode() . substr(time(), -4);
    }

    /**
     * Get invite code by code string.
     *
     * @param string $code
     * @return array|false
     */
    public function getByCode($code) {
        $code = strtoupper(trim($code));
        if (empty($code)) {
            return false;
        }

        return $this->SQL
            ->select('*')
            ->from('InviteCode')
            ->where('Code', $code)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);
    }

    /**
     * Validate an invite code for registration.
     *
     * @param string $code
     * @return array ['valid', 'error', 'message', 'inviteCode']
     */
    public function validateCode($code) {
        $code = strtoupper(trim($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'error' => 'Empty',
                'message' => t('Please enter an invite code.', '请输入邀请码')
            ];
        }

        $inviteCode = $this->getByCode($code);

        if (!$inviteCode) {
            return [
                'valid' => false,
                'error' => 'NotFound',
                'message' => t('Invalid invite code.', '邀请码无效')
            ];
        }

        if (!$inviteCode['IsActive']) {
            return [
                'valid' => false,
                'error' => 'Disabled',
                'message' => t('This invite code has been disabled.', '此邀请码已被禁用')
            ];
        }

        // Check expiration
        if ($inviteCode['ExpiresAt'] !== null) {
            $expiresAt = strtotime($inviteCode['ExpiresAt']);
            if ($expiresAt < time()) {
                return [
                    'valid' => false,
                    'error' => 'Expired',
                    'message' => t('This invite code has expired.', '此邀请码已过期')
                ];
            }
        }

        // Check usage limit
        if ($inviteCode['UseCount'] >= $inviteCode['MaxUses']) {
            return [
                'valid' => false,
                'error' => 'Exhausted',
                'message' => t('This invite code has reached its usage limit.', '此邀请码已达到使用上限')
            ];
        }

        return [
            'valid' => true,
            'inviteCode' => $inviteCode
        ];
    }

    /**
     * Use an invite code (increment use count and link to user).
     *
     * @param string $code
     * @param int $userID The newly registered user ID
     * @return bool
     */
    public function useCode($code, $userID) {
        $inviteCode = $this->getByCode($code);
        if (!$inviteCode) {
            return false;
        }

        $inviteCodeID = $inviteCode['InviteCodeID'];
        $creatorUserID = (int)$inviteCode['CreatorUserID'];

        // Increment use count
        $this->SQL
            ->update('InviteCode')
            ->set('UseCount', 'UseCount + 1', false)
            ->where('InviteCodeID', $inviteCodeID)
            ->put();

        // Update user record
        Gdn::userModel()->setField($userID, 'InviteCodeID', $inviteCodeID);
        if ($creatorUserID > 0) {
            Gdn::userModel()->setField($userID, 'InvitedByUserID', $creatorUserID);

            // Award bonus to inviter if configured
            $inviterBonus = self::getInviterBonus();
            if ($inviterBonus > 0) {
                $inviter = Gdn::userModel()->getID($creatorUserID);
                if ($inviter) {
                    $inviterBalance = (int)val('Points', $inviter, 0);
                    $newBalance = $inviterBalance + $inviterBonus;
                    Gdn::userModel()->setField($creatorUserID, 'Points', $newBalance);

                    // Log bonus
                    $newUser = Gdn::userModel()->getID($userID);
                    Gdn::sql()->insert('CreditLog', [
                        'UserID' => $creatorUserID,
                        'Amount' => $inviterBonus,
                        'Balance' => $newBalance,
                        'Type' => 'invite_bonus',
                        'RelatedID' => $inviteCodeID,
                        'RelatedUserID' => $userID,
                        'Note' => sprintf(t('Invite bonus: %s registered', '邀请奖励: %s 已注册'), val('Name', $newUser, '')),
                        'DateInserted' => Gdn_Format::toDateTime()
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Get invite codes created by a user.
     *
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserCodes($userID, $limit = 20, $offset = 0) {
        return $this->SQL
            ->select('*')
            ->from('InviteCode')
            ->where('CreatorUserID', (int)$userID)
            ->orderBy('DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Get count of invite codes created by a user.
     *
     * @param int $userID
     * @return int
     */
    public function getUserCodesCount($userID) {
        return (int)$this->SQL
            ->select('InviteCodeID', 'count', 'Count')
            ->from('InviteCode')
            ->where('CreatorUserID', (int)$userID)
            ->get()
            ->value('Count', 0);
    }

    /**
     * Get users invited by a specific user.
     *
     * @param int $userID The inviter's user ID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getInvitedUsers($userID, $limit = 20, $offset = 0) {
        return $this->SQL
            ->select('u.UserID, u.Name, u.Photo, u.DateInserted, ic.Code')
            ->from('User u')
            ->join('InviteCode ic', 'u.InviteCodeID = ic.InviteCodeID')
            ->where('u.InvitedByUserID', (int)$userID)
            ->orderBy('u.DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Get count of users invited by a specific user.
     *
     * @param int $userID
     * @return int
     */
    public function getInvitedUsersCount($userID) {
        return (int)$this->SQL
            ->select('UserID', 'count', 'Count')
            ->from('User')
            ->where('InvitedByUserID', (int)$userID)
            ->get()
            ->value('Count', 0);
    }

    /**
     * Get all invite codes (for admin).
     *
     * @param array $filters ['search', 'status', 'creator']
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllCodes($filters = [], $limit = 20, $offset = 0) {
        $sql = $this->SQL
            ->select('ic.*')
            ->select('u.Name', '', 'CreatorName')
            ->from('InviteCode ic')
            ->join('User u', 'ic.CreatorUserID = u.UserID', 'left');

        // Apply filters
        if (!empty($filters['search'])) {
            $sql->where('ic.Code', $filters['search']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql->where('ic.IsActive', 1)
                    ->beginWhereGroup()
                    ->where('ic.ExpiresAt', null)
                    ->orWhere('ic.ExpiresAt >', Gdn_Format::toDateTime())
                    ->endWhereGroup()
                    ->where('ic.UseCount <', 'ic.MaxUses', false, false);
            } elseif ($filters['status'] === 'expired') {
                $sql->where('ic.ExpiresAt <', Gdn_Format::toDateTime());
            } elseif ($filters['status'] === 'exhausted') {
                $sql->where('ic.UseCount >=', 'ic.MaxUses', false, false);
            } elseif ($filters['status'] === 'disabled') {
                $sql->where('ic.IsActive', 0);
            }
        }

        if (isset($filters['creator'])) {
            $sql->where('ic.CreatorUserID', (int)$filters['creator']);
        }

        return $sql
            ->orderBy('ic.DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Get count of all invite codes.
     *
     * @param array $filters
     * @return int
     */
    public function getAllCodesCount($filters = []) {
        $sql = $this->SQL
            ->select('ic.InviteCodeID', 'count', 'Count')
            ->from('InviteCode ic');

        // Apply same filters as getAllCodes
        if (!empty($filters['search'])) {
            $sql->where('ic.Code', $filters['search']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql->where('ic.IsActive', 1)
                    ->beginWhereGroup()
                    ->where('ic.ExpiresAt', null)
                    ->orWhere('ic.ExpiresAt >', Gdn_Format::toDateTime())
                    ->endWhereGroup()
                    ->where('ic.UseCount <', 'ic.MaxUses', false, false);
            } elseif ($filters['status'] === 'expired') {
                $sql->where('ic.ExpiresAt <', Gdn_Format::toDateTime());
            } elseif ($filters['status'] === 'exhausted') {
                $sql->where('ic.UseCount >=', 'ic.MaxUses', false, false);
            } elseif ($filters['status'] === 'disabled') {
                $sql->where('ic.IsActive', 0);
            }
        }

        if (isset($filters['creator'])) {
            $sql->where('ic.CreatorUserID', (int)$filters['creator']);
        }

        return (int)$sql->get()->value('Count', 0);
    }

    /**
     * Get users registered with a specific invite code.
     *
     * @param int $inviteCodeID
     * @return array
     */
    public function getCodeUsers($inviteCodeID) {
        return $this->SQL
            ->select('UserID, Name, Photo, DateInserted')
            ->from('User')
            ->where('InviteCodeID', (int)$inviteCodeID)
            ->orderBy('DateInserted', 'desc')
            ->get()
            ->resultArray();
    }

    /**
     * Toggle invite code active status.
     *
     * @param int $inviteCodeID
     * @return bool New status
     */
    public function toggleActive($inviteCodeID) {
        $code = $this->getID($inviteCodeID);
        if (!$code) {
            return false;
        }

        $newStatus = $code['IsActive'] ? 0 : 1;

        $this->SQL
            ->update('InviteCode')
            ->set('IsActive', $newStatus)
            ->where('InviteCodeID', $inviteCodeID)
            ->put();

        return (bool)$newStatus;
    }

    /**
     * Get statistics for admin dashboard.
     *
     * @return array
     */
    public function getStatistics() {
        $now = Gdn_Format::toDateTime();

        // Total codes
        $total = (int)$this->SQL
            ->select('InviteCodeID', 'count', 'Count')
            ->from('InviteCode')
            ->get()
            ->value('Count', 0);

        // Active codes (not expired, not exhausted, is active)
        $active = (int)$this->SQL
            ->select('InviteCodeID', 'count', 'Count')
            ->from('InviteCode')
            ->where('IsActive', 1)
            ->beginWhereGroup()
            ->where('ExpiresAt', null)
            ->orWhere('ExpiresAt >', $now)
            ->endWhereGroup()
            ->where('UseCount <', 'MaxUses', false, false)
            ->get()
            ->value('Count', 0);

        // Expired codes
        $expired = (int)$this->SQL
            ->select('InviteCodeID', 'count', 'Count')
            ->from('InviteCode')
            ->where('ExpiresAt <', $now)
            ->get()
            ->value('Count', 0);

        // Exhausted codes
        $exhausted = (int)$this->SQL
            ->select('InviteCodeID', 'count', 'Count')
            ->from('InviteCode')
            ->where('UseCount >=', 'MaxUses', false, false)
            ->get()
            ->value('Count', 0);

        // Total users registered via invite
        $totalInvited = (int)$this->SQL
            ->select('UserID', 'count', 'Count')
            ->from('User')
            ->where('InviteCodeID >', 0)
            ->get()
            ->value('Count', 0);

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'exhausted' => $exhausted,
            'totalInvited' => $totalInvited
        ];
    }

    /**
     * Database structure definition.
     *
     * Called from BitsmeshThemeHooks::structure()
     */
    public static function structure() {
        $construct = Gdn::structure();

        // Create InviteCode table
        $construct->table('InviteCode')
            ->primaryKey('InviteCodeID')
            ->column('Code', 'varchar(32)', false, 'unique')
            ->column('CreatorUserID', 'int', false, 'index')
            ->column('MaxUses', 'int', 1)
            ->column('UseCount', 'int', 0)
            ->column('ExpiresAt', 'datetime', true, 'index')
            ->column('CreditCost', 'int', 0)
            ->column('DateInserted', 'datetime', false, 'index')
            ->column('IsActive', 'tinyint', 1, 'index')
            ->set(false, false);

        // Add fields to User table
        $construct->table('User')
            ->column('InviteCodeID', 'int', true, 'index')
            ->column('InvitedByUserID', 'int', true, 'index')
            ->set(false, false);
    }
}
