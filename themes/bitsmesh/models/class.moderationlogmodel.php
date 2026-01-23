<?php
/**
 * Moderation Log Model
 *
 * Manages the moderation log records for public display.
 * Stores admin actions like move, delete, ban, etc.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

/**
 * Class ModerationLogModel
 *
 * Handles CRUD operations for moderation log entries.
 */
class ModerationLogModel extends Gdn_Model {

    /** Records per page */
    const RECORDS_PER_PAGE = 20;

    /** Action types */
    const ACTION_MOVE = 'Move';
    const ACTION_DELETE = 'Delete';
    const ACTION_LOCK = 'Lock';
    const ACTION_BAN = 'Ban';
    const ACTION_AWARD = 'Award';
    const ACTION_PENALTY = 'Penalty';
    const ACTION_EDIT = 'Edit';
    const ACTION_OTHER = 'Other';

    /** Record types */
    const RECORD_DISCUSSION = 'Discussion';
    const RECORD_COMMENT = 'Comment';
    const RECORD_USER = 'User';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('ModerationLog');
        $this->PrimaryKey = 'ModerationLogID';
    }

    /**
     * Create the database table structure.
     *
     * @return void
     */
    public static function structure() {
        $database = Gdn::database();
        $construct = $database->structure();

        $construct->table('ModerationLog')
            ->primaryKey('ModerationLogID')
            ->column('ActionType', 'varchar(20)', false, 'index.ActionType')  // Move, Delete, Lock, Ban, Award, Penalty, Edit, Other
            ->column('RecordType', 'varchar(20)', false, 'index.RecordType')  // Discussion, Comment, User
            ->column('RecordID', 'int', false, 'index.RecordID')              // DiscussionID, CommentID, or UserID
            ->column('RecordUserID', 'int', true, 'index.RecordUserID')       // The user who owns the record
            ->column('RecordTitle', 'varchar(255)', true)                      // Discussion name or user name for display
            ->column('RecordUrl', 'varchar(500)', true)                        // Direct link to the record
            ->column('Reason', 'text', true)                                   // Admin reason for the action
            ->column('Actions', 'text', true)                                  // JSON array of action details
            ->column('PointsChange', 'int', '0')                              // Points added or removed (chicken legs)
            ->column('CategoryID', 'int', true, 'index.CategoryID')           // For move actions - target category
            ->column('CategoryName', 'varchar(100)', true)                    // Target category name for display
            ->column('BanDays', 'int', '0')                                   // Ban duration in days (0 = no ban)
            ->column('IsPublic', 'tinyint', '1')                              // Whether to show in public log
            ->column('InsertUserID', 'int', false, 'index.InsertUserID')      // Admin who performed the action
            ->column('DateInserted', 'datetime', false, 'index.DateInserted')
            ->set(false, false);

        // Add index for efficient pagination queries
        $sql = Gdn::sql();
        $indexName = 'IX_ModerationLog_Public_Date';
        $existingIndex = $sql->query("SHOW INDEX FROM {$sql->Database->DatabasePrefix}ModerationLog WHERE Key_name = '$indexName'")->resultArray();
        if (empty($existingIndex)) {
            $sql->query("ALTER TABLE {$sql->Database->DatabasePrefix}ModerationLog ADD INDEX $indexName (IsPublic, DateInserted DESC)");
        }
    }

    /**
     * Get paginated moderation logs for public display.
     *
     * @param int $page Page number (1-based)
     * @param int $limit Records per page
     * @return array ['logs' => array, 'total' => int, 'pageCount' => int]
     */
    public function getPublicLogs($page = 1, $limit = self::RECORDS_PER_PAGE) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        // Get total count
        $total = $this->SQL
            ->select('ModerationLogID', 'count', 'Total')
            ->from('ModerationLog')
            ->where('IsPublic', 1)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);
        $totalCount = $total ? (int)$total['Total'] : 0;
        $pageCount = ceil($totalCount / $limit);

        // Get logs with admin user info
        $logs = $this->SQL
            ->select('ml.*')
            ->select('u.Name', '', 'AdminName')
            ->select('u.Photo', '', 'AdminPhoto')
            ->select('ru.Name', '', 'RecordUserName')
            ->select('ru.Photo', '', 'RecordUserPhoto')
            ->from('ModerationLog ml')
            ->join('User u', 'ml.InsertUserID = u.UserID', 'left')
            ->join('User ru', 'ml.RecordUserID = ru.UserID', 'left')
            ->where('ml.IsPublic', 1)
            ->orderBy('ml.DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->resultArray();

        // Decode JSON Actions field
        foreach ($logs as &$log) {
            if (!empty($log['Actions'])) {
                $log['ActionsArray'] = json_decode($log['Actions'], true) ?? [];
            } else {
                $log['ActionsArray'] = [];
            }
        }

        return [
            'logs' => $logs,
            'total' => $totalCount,
            'pageCount' => $pageCount,
            'currentPage' => $page,
            'perPage' => $limit
        ];
    }

    /**
     * Get a single log entry by ID.
     *
     * @param int $logID
     * @return array|null
     */
    public function getLogByID($logID) {
        $log = $this->SQL
            ->select('ml.*')
            ->select('u.Name', '', 'AdminName')
            ->select('u.Photo', '', 'AdminPhoto')
            ->select('ru.Name', '', 'RecordUserName')
            ->from('ModerationLog ml')
            ->join('User u', 'ml.InsertUserID = u.UserID', 'left')
            ->join('User ru', 'ml.RecordUserID = ru.UserID', 'left')
            ->where('ml.ModerationLogID', $logID)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);

        if ($log && !empty($log['Actions'])) {
            $log['ActionsArray'] = json_decode($log['Actions'], true) ?? [];
        }

        return $log;
    }

    /**
     * Add a new moderation log entry.
     *
     * @param array $data Log data
     * @return int|false Log ID on success, false on failure
     */
    public function addLog($data) {
        // Validate required fields
        $required = ['ActionType', 'RecordType', 'RecordID'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        // Set defaults
        $logData = [
            'ActionType' => $data['ActionType'],
            'RecordType' => $data['RecordType'],
            'RecordID' => (int)$data['RecordID'],
            'RecordUserID' => $data['RecordUserID'] ?? null,
            'RecordTitle' => $data['RecordTitle'] ?? null,
            'RecordUrl' => $data['RecordUrl'] ?? null,
            'Reason' => $data['Reason'] ?? null,
            'Actions' => isset($data['Actions']) ? json_encode($data['Actions']) : null,
            'PointsChange' => (int)($data['PointsChange'] ?? 0),
            'CategoryID' => $data['CategoryID'] ?? null,
            'CategoryName' => $data['CategoryName'] ?? null,
            'BanDays' => (int)($data['BanDays'] ?? 0),
            'IsPublic' => isset($data['IsPublic']) ? (int)$data['IsPublic'] : 1,
            'InsertUserID' => $data['InsertUserID'] ?? Gdn::session()->UserID,
            'DateInserted' => $data['DateInserted'] ?? Gdn_Format::toDateTime()
        ];

        return $this->SQL->insert('ModerationLog', $logData);
    }

    /**
     * Format action type for display.
     *
     * @param string $actionType
     * @return string
     */
    public static function formatActionType($actionType) {
        $map = [
            self::ACTION_MOVE => t('ActionType.Move', '移动'),
            self::ACTION_DELETE => t('ActionType.Delete', '删除'),
            self::ACTION_LOCK => t('ActionType.Lock', '锁定'),
            self::ACTION_BAN => t('ActionType.Ban', '禁言'),
            self::ACTION_AWARD => t('ActionType.Award', '奖励'),
            self::ACTION_PENALTY => t('ActionType.Penalty', '处罚'),
            self::ACTION_EDIT => t('ActionType.Edit', '编辑'),
            self::ACTION_OTHER => t('ActionType.Other', '其他'),
        ];
        return $map[$actionType] ?? $actionType;
    }

    /**
     * Format record type for display.
     *
     * @param string $recordType
     * @return string
     */
    public static function formatRecordType($recordType) {
        $map = [
            self::RECORD_DISCUSSION => t('RecordType.Discussion', '帖子'),
            self::RECORD_COMMENT => t('RecordType.Comment', '评论'),
            self::RECORD_USER => t('RecordType.User', '用户'),
        ];
        return $map[$recordType] ?? $recordType;
    }

    /**
     * Build action summary text for display.
     *
     * @param array $log The log entry
     * @return string
     */
    public static function buildActionSummary($log) {
        $parts = [];

        // Reason with points
        if (!empty($log['Reason'])) {
            $prefix = '';
            if ($log['PointsChange'] != 0) {
                $prefix = '因"' . $log['Reason'] . '"被' . ($log['PointsChange'] > 0 ? '+' : '') . $log['PointsChange'] . '鸡腿';
            } else {
                $prefix = $log['Reason'];
            }
            $parts[] = $prefix;
        } elseif ($log['PointsChange'] != 0) {
            $parts[] = ($log['PointsChange'] > 0 ? '+' : '') . $log['PointsChange'] . '鸡腿';
        }

        // Category move
        if (!empty($log['CategoryName']) && $log['ActionType'] === self::ACTION_MOVE) {
            $parts[] = '移动到 ' . $log['CategoryName'] . ' 版块';
        }

        // Action details from JSON
        if (!empty($log['ActionsArray'])) {
            foreach ($log['ActionsArray'] as $action) {
                if (!empty($action)) {
                    $parts[] = $action;
                }
            }
        }

        // Ban days
        if ($log['BanDays'] > 0) {
            $parts[] = '用户被禁言' . $log['BanDays'] . '天';
        }

        return implode("\n", $parts);
    }
}
