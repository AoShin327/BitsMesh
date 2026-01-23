<?php
/**
 * ChickenLeg Model
 *
 * Handles chicken leg (appreciation) feature for BitsMesh theme.
 * Each user gets 1 free chicken leg per day to give to any post.
 *
 * @package BitsMesh
 * @since 1.0
 */

/**
 * ChickenLegModel - Manages chicken leg gifts.
 *
 * Database Tables:
 * - GDN_ChickenLeg: Tracks who gave chicken legs to what content
 * - GDN_Discussion: CountChickenLegs field for aggregate
 * - GDN_Comment: CountChickenLegs field for aggregate
 *
 * Rules:
 * - Each user gets 1 free chicken leg per day (global, not per-post)
 * - Cannot give chicken leg to own content
 * - Multiple chicken legs to same content allowed (on different days)
 */
class ChickenLegModel extends Gdn_Model {

    /** @var int Daily free quota per user */
    const DAILY_FREE_QUOTA = 1;

    /** @var ChickenLegModel Singleton instance */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('ChickenLeg');
        $this->PrimaryKey = 'ChickenLegID';
    }

    /**
     * Get singleton instance.
     *
     * @return ChickenLegModel
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new ChickenLegModel();
        }
        return self::$instance;
    }

    /**
     * Get user's remaining free chicken legs for today.
     *
     * @param int $userID User ID
     * @return int Remaining free quota
     */
    public function getRemainingQuota($userID) {
        if (!$userID) {
            return 0;
        }

        $userID = (int)$userID;
        $today = date('Y-m-d');

        // Count today's chicken legs given by this user
        $usedToday = $this->SQL
            ->select('ChickenLegID', 'count', 'Count')
            ->from('ChickenLeg')
            ->where('UserID', $userID)
            ->where('DateInserted >=', $today . ' 00:00:00')
            ->where('DateInserted <=', $today . ' 23:59:59')
            ->get()
            ->firstRow();

        $usedCount = $usedToday ? (int)$usedToday->Count : 0;

        return max(0, self::DAILY_FREE_QUOTA - $usedCount);
    }

    /**
     * Check if user can give a chicken leg today.
     *
     * @param int $userID User ID
     * @return bool
     */
    public function canGiveToday($userID) {
        return $this->getRemainingQuota($userID) > 0;
    }

    /**
     * Give a chicken leg to a discussion or comment.
     *
     * @param int $userID User giving the chicken leg
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Discussion ID or Comment ID
     * @return array Result with 'success', 'error', 'newCount', 'remainingQuota'
     */
    public function giveChickenLeg($userID, $recordType, $recordID) {
        $userID = (int)$userID;
        $recordID = (int)$recordID;
        $recordType = ucfirst(strtolower($recordType));

        // Validate inputs
        if (!$userID) {
            return ['success' => false, 'error' => 'NotLoggedIn'];
        }

        if (!in_array($recordType, ['Discussion', 'Comment'])) {
            return ['success' => false, 'error' => 'InvalidRecordType'];
        }

        if (!$recordID) {
            return ['success' => false, 'error' => 'InvalidRecordID'];
        }

        // Check daily quota
        if (!$this->canGiveToday($userID)) {
            return [
                'success' => false,
                'error' => 'NoQuota',
                'message' => t('You have no chicken legs left today. Come back tomorrow!', '今日鸡腿已用完，明天再来吧！'),
                'remainingQuota' => 0
            ];
        }

        // Get the record to verify it exists and get author
        $record = $this->getRecord($recordType, $recordID);
        if (!$record) {
            return ['success' => false, 'error' => 'RecordNotFound'];
        }

        // Check if user is trying to give to their own content
        $authorID = (int)($record['InsertUserID'] ?? 0);
        if ($authorID === $userID) {
            return [
                'success' => false,
                'error' => 'SelfGift',
                'message' => t('You cannot give chicken legs to your own content.', '不能给自己的内容加鸡腿哦~')
            ];
        }

        // Insert the chicken leg record
        $this->SQL->insert('ChickenLeg', [
            'UserID' => $userID,
            'RecordType' => $recordType,
            'RecordID' => $recordID,
            'ReceiverUserID' => $authorID,
            'DateInserted' => Gdn_Format::toDateTime()
        ]);

        // Update aggregate count
        $newCount = $this->updateRecordCount($recordType, $recordID);

        return [
            'success' => true,
            'newCount' => $newCount,
            'remainingQuota' => $this->getRemainingQuota($userID),
            'message' => t('Chicken leg sent!', '鸡腿已送出！')
        ];
    }

    /**
     * Get a discussion or comment record.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return array|false Record data or false
     */
    private function getRecord($recordType, $recordID) {
        $table = $recordType;
        $primaryKey = $recordType . 'ID';

        return $this->SQL
            ->select('*')
            ->from($table)
            ->where($primaryKey, (int)$recordID)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);
    }

    /**
     * Update chicken leg count on a discussion or comment.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return int New count
     */
    private function updateRecordCount($recordType, $recordID) {
        $recordID = (int)$recordID;

        // Count chicken legs for this record
        $count = $this->SQL
            ->select('ChickenLegID', 'count', 'Count')
            ->from('ChickenLeg')
            ->where('RecordType', $recordType)
            ->where('RecordID', $recordID)
            ->get()
            ->firstRow();

        $newCount = $count ? (int)$count->Count : 0;

        // Update the record
        $table = $recordType;
        $primaryKey = $recordType . 'ID';

        $this->SQL
            ->update($table)
            ->set('CountChickenLegs', $newCount)
            ->where($primaryKey, $recordID)
            ->put();

        return $newCount;
    }

    /**
     * Get chicken leg count for a record.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return int Chicken leg count
     */
    public function getChickenLegCount($recordType, $recordID) {
        $count = $this->SQL
            ->select('ChickenLegID', 'count', 'Count')
            ->from('ChickenLeg')
            ->where('RecordType', ucfirst(strtolower($recordType)))
            ->where('RecordID', (int)$recordID)
            ->get()
            ->firstRow();

        return $count ? (int)$count->Count : 0;
    }

    /**
     * Get chicken leg counts for multiple records.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param array $recordIDs Record IDs
     * @return array Map of RecordID => Count
     */
    public function getChickenLegCounts($recordType, array $recordIDs) {
        if (empty($recordIDs)) {
            return [];
        }

        $recordType = ucfirst(strtolower($recordType));
        $recordIDs = array_map('intval', $recordIDs);

        $results = $this->SQL
            ->select('RecordID, COUNT(*) as Count')
            ->from('ChickenLeg')
            ->where('RecordType', $recordType)
            ->whereIn('RecordID', $recordIDs)
            ->groupBy('RecordID')
            ->get()
            ->resultArray();

        // Initialize result map
        $counts = [];
        foreach ($recordIDs as $id) {
            $counts[$id] = 0;
        }

        // Populate counts
        foreach ($results as $row) {
            $counts[(int)$row['RecordID']] = (int)$row['Count'];
        }

        return $counts;
    }

    /**
     * Check if user has given a chicken leg to a record today.
     *
     * @param int $userID User ID
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return bool
     */
    public function hasGivenToday($userID, $recordType, $recordID) {
        $today = date('Y-m-d');

        $existing = $this->SQL
            ->select('ChickenLegID')
            ->from('ChickenLeg')
            ->where('UserID', (int)$userID)
            ->where('RecordType', ucfirst(strtolower($recordType)))
            ->where('RecordID', (int)$recordID)
            ->where('DateInserted >=', $today . ' 00:00:00')
            ->where('DateInserted <=', $today . ' 23:59:59')
            ->get()
            ->firstRow();

        return !empty($existing);
    }

    /**
     * Get user's chicken leg history.
     *
     * @param int $userID User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Chicken leg records
     */
    public function getUserHistory($userID, $limit = 20, $offset = 0) {
        return $this->SQL
            ->select('*')
            ->from('ChickenLeg')
            ->where('UserID', (int)$userID)
            ->orderBy('DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Get chicken legs received by a user.
     *
     * @param int $userID User ID (receiver)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Chicken leg records
     */
    public function getReceivedHistory($userID, $limit = 20, $offset = 0) {
        return $this->SQL
            ->select('*')
            ->from('ChickenLeg')
            ->where('ReceiverUserID', (int)$userID)
            ->orderBy('DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();
    }

    /**
     * Database structure definition.
     *
     * Called from BitsmeshThemeHooks::structure()
     */
    public static function structure() {
        $construct = Gdn::structure();

        $construct->table('ChickenLeg')
            ->primaryKey('ChickenLegID')
            ->column('UserID', 'int', false, 'index.UserID')
            ->column('RecordType', 'varchar(20)', false, 'index.RecordType')
            ->column('RecordID', 'int', false, 'index.RecordID')
            ->column('ReceiverUserID', 'int', false, 'index.ReceiverUserID')
            ->column('DateInserted', 'datetime', false, 'index.Date')
            ->set(false, false);
    }
}
