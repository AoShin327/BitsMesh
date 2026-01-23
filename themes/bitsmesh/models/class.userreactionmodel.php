<?php
/**
 * UserReaction Model
 *
 * Handles reactions (like/dislike) for both Discussion and Comment.
 * Unified model to replace separate handling of Discussion and Comment reactions.
 *
 * @package BitsMesh
 * @since 1.0
 */

/**
 * UserReactionModel - Manages user reactions to discussions and comments.
 *
 * Database Tables:
 * - GDN_UserReaction: Stores individual user reactions (UserID, RecordType, RecordID, Score)
 *
 * Score Values:
 * - 1: Like
 * - -1: Dislike
 * - 0: Neutral (reaction removed)
 */
class UserReactionModel extends Gdn_Model {

    /** @var UserReactionModel Singleton instance */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('UserReaction');
        $this->PrimaryKey = 'UserReactionID';
    }

    /**
     * Get singleton instance.
     *
     * @return UserReactionModel
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new UserReactionModel();
        }
        return self::$instance;
    }

    /**
     * Get user's reaction to a specific record.
     *
     * @param int $userID User ID
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return array|false User reaction record or false if not found
     */
    public function getUserReaction($userID, $recordType, $recordID) {
        if (!$userID || !$recordType || !$recordID) {
            return false;
        }

        $recordType = ucfirst(strtolower($recordType));

        return $this->SQL
            ->select('*')
            ->from('UserReaction')
            ->where('UserID', (int)$userID)
            ->where('RecordType', $recordType)
            ->where('RecordID', (int)$recordID)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);
    }

    /**
     * Set user's reaction to a record (like or dislike).
     *
     * This method handles:
     * 1. Creating new reaction
     * 2. Toggling off existing reaction (same score)
     * 3. Switching reaction (like to dislike or vice versa)
     *
     * @param int $userID User ID
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @param int $score Reaction score (1 = like, -1 = dislike)
     * @return array Result with 'success', 'action', 'newScore', 'likeCount', 'dislikeCount'
     */
    public function setReaction($userID, $recordType, $recordID, $score) {
        // Validate inputs
        $userID = (int)$userID;
        $recordID = (int)$recordID;
        $score = (int)$score;
        $recordType = ucfirst(strtolower($recordType));

        if (!$userID) {
            return ['success' => false, 'error' => 'NotLoggedIn'];
        }

        if (!in_array($recordType, ['Discussion', 'Comment'])) {
            return ['success' => false, 'error' => 'InvalidRecordType'];
        }

        if (!$recordID) {
            return ['success' => false, 'error' => 'InvalidRecordID'];
        }

        if (!in_array($score, [1, -1])) {
            return ['success' => false, 'error' => 'InvalidScore'];
        }

        // Verify record exists
        if (!$this->recordExists($recordType, $recordID)) {
            return ['success' => false, 'error' => 'RecordNotFound'];
        }

        // Get current reaction
        $existing = $this->getUserReaction($userID, $recordType, $recordID);
        $action = 'added';

        if ($existing) {
            $oldScore = (int)$existing['Score'];

            if ($oldScore === $score) {
                // Same reaction: toggle off (remove)
                $this->SQL
                    ->delete('UserReaction', [
                        'UserID' => $userID,
                        'RecordType' => $recordType,
                        'RecordID' => $recordID
                    ]);
                $action = 'removed';
                $newScore = 0;
            } else {
                // Different reaction: switch
                $this->SQL
                    ->update('UserReaction')
                    ->set('Score', $score)
                    ->set('DateInserted', Gdn_Format::toDateTime())
                    ->where('UserID', $userID)
                    ->where('RecordType', $recordType)
                    ->where('RecordID', $recordID)
                    ->put();
                $action = 'switched';
                $newScore = $score;
            }
        } else {
            // New reaction
            $this->SQL->insert('UserReaction', [
                'UserID' => $userID,
                'RecordType' => $recordType,
                'RecordID' => $recordID,
                'Score' => $score,
                'DateInserted' => Gdn_Format::toDateTime()
            ]);
            $newScore = $score;
        }

        // Update aggregate counts
        $counts = $this->getReactionCounts($recordType, $recordID);

        // Update Score field on the record
        $this->updateRecordScore($recordType, $recordID, $counts);

        return [
            'success' => true,
            'action' => $action,
            'newScore' => $newScore,
            'likeCount' => $counts['likeCount'],
            'dislikeCount' => $counts['dislikeCount'],
            'netScore' => $counts['likeCount'] - $counts['dislikeCount']
        ];
    }

    /**
     * Check if a record exists.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return bool
     */
    private function recordExists($recordType, $recordID) {
        $table = $recordType;
        $primaryKey = $recordType . 'ID';

        $record = $this->SQL
            ->select($primaryKey)
            ->from($table)
            ->where($primaryKey, (int)$recordID)
            ->get()
            ->firstRow();

        return !empty($record);
    }

    /**
     * Update the Score field on a Discussion or Comment record.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @param array $counts Reaction counts
     */
    private function updateRecordScore($recordType, $recordID, $counts) {
        $table = $recordType;
        $primaryKey = $recordType . 'ID';
        $netScore = $counts['likeCount'] - $counts['dislikeCount'];

        $this->SQL
            ->update($table)
            ->set('Score', $netScore)
            ->where($primaryKey, (int)$recordID)
            ->put();
    }

    /**
     * Get reaction counts for a record.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param int $recordID Record ID
     * @return array ['likeCount' => int, 'dislikeCount' => int]
     */
    public function getReactionCounts($recordType, $recordID) {
        $recordType = ucfirst(strtolower($recordType));
        $recordID = (int)$recordID;

        // Count likes
        $likeCount = $this->SQL
            ->select('UserReactionID', 'count', 'Count')
            ->from('UserReaction')
            ->where('RecordType', $recordType)
            ->where('RecordID', $recordID)
            ->where('Score', 1)
            ->get()
            ->firstRow();
        $likeCount = $likeCount ? (int)$likeCount->Count : 0;

        // Count dislikes
        $dislikeCount = $this->SQL
            ->select('UserReactionID', 'count', 'Count')
            ->from('UserReaction')
            ->where('RecordType', $recordType)
            ->where('RecordID', $recordID)
            ->where('Score', -1)
            ->get()
            ->firstRow();
        $dislikeCount = $dislikeCount ? (int)$dislikeCount->Count : 0;

        return [
            'likeCount' => $likeCount,
            'dislikeCount' => $dislikeCount
        ];
    }

    /**
     * Get user's reactions for multiple records.
     *
     * @param int $userID User ID
     * @param string $recordType 'Discussion' or 'Comment'
     * @param array $recordIDs Array of record IDs
     * @return array Map of RecordID => Score
     */
    public function getUserReactions($userID, $recordType, array $recordIDs) {
        if (!$userID || empty($recordIDs)) {
            return [];
        }

        $recordType = ucfirst(strtolower($recordType));

        $reactions = $this->SQL
            ->select('RecordID, Score')
            ->from('UserReaction')
            ->where('UserID', (int)$userID)
            ->where('RecordType', $recordType)
            ->whereIn('RecordID', array_map('intval', $recordIDs))
            ->get()
            ->resultArray();

        $result = [];
        foreach ($reactions as $reaction) {
            $result[(int)$reaction['RecordID']] = (int)$reaction['Score'];
        }

        return $result;
    }

    /**
     * Get reaction counts for multiple records.
     *
     * @param string $recordType 'Discussion' or 'Comment'
     * @param array $recordIDs Array of record IDs
     * @return array Map of RecordID => ['likeCount' => int, 'dislikeCount' => int]
     */
    public function getBulkReactionCounts($recordType, array $recordIDs) {
        if (empty($recordIDs)) {
            return [];
        }

        $recordType = ucfirst(strtolower($recordType));
        $recordIDs = array_map('intval', $recordIDs);

        // Get all reactions for these records
        $reactions = $this->SQL
            ->select('RecordID, Score, COUNT(*) as Count')
            ->from('UserReaction')
            ->where('RecordType', $recordType)
            ->whereIn('RecordID', $recordIDs)
            ->groupBy('RecordID, Score')
            ->get()
            ->resultArray();

        // Build result map
        $result = [];
        foreach ($recordIDs as $id) {
            $result[$id] = ['likeCount' => 0, 'dislikeCount' => 0];
        }

        foreach ($reactions as $reaction) {
            $recordID = (int)$reaction['RecordID'];
            $score = (int)$reaction['Score'];
            $count = (int)$reaction['Count'];

            if ($score === 1) {
                $result[$recordID]['likeCount'] = $count;
            } elseif ($score === -1) {
                $result[$recordID]['dislikeCount'] = $count;
            }
        }

        return $result;
    }

    /**
     * Get all reactions for a discussion page (discussion + all comments).
     *
     * @param int $discussionID Discussion ID
     * @param int|null $userID User ID (optional, for user-specific reactions)
     * @return array Structured reaction data
     */
    public function getDiscussionPageReactions($discussionID, $userID = null) {
        $discussionID = (int)$discussionID;

        // Get comment IDs for this discussion
        $comments = $this->SQL
            ->select('CommentID')
            ->from('Comment')
            ->where('DiscussionID', $discussionID)
            ->get()
            ->resultArray();

        $commentIDs = array_column($comments, 'CommentID');

        // Get reaction counts
        $discussionCounts = $this->getReactionCounts('Discussion', $discussionID);
        $commentCounts = $this->getBulkReactionCounts('Comment', $commentIDs);

        // Get user reactions if logged in
        $userDiscussionReaction = null;
        $userCommentReactions = [];

        if ($userID) {
            $existing = $this->getUserReaction($userID, 'Discussion', $discussionID);
            $userDiscussionReaction = $existing ? (int)$existing['Score'] : 0;
            $userCommentReactions = $this->getUserReactions($userID, 'Comment', $commentIDs);
        }

        // Build result
        $result = [];

        // Discussion (represented as 'discussion-{id}')
        $result['discussion'] = [
            'likeCount' => $discussionCounts['likeCount'],
            'dislikeCount' => $discussionCounts['dislikeCount'],
            'userScore' => $userDiscussionReaction ?? 0
        ];

        // Comments
        foreach ($commentIDs as $commentID) {
            $result[$commentID] = [
                'likeCount' => $commentCounts[$commentID]['likeCount'] ?? 0,
                'dislikeCount' => $commentCounts[$commentID]['dislikeCount'] ?? 0,
                'userScore' => $userCommentReactions[$commentID] ?? 0
            ];
        }

        return $result;
    }

    /**
     * Database structure definition.
     *
     * Called from BitsmeshThemeHooks::structure()
     */
    public static function structure() {
        $construct = Gdn::structure();

        $construct->table('UserReaction')
            ->primaryKey('UserReactionID')
            ->column('UserID', 'int', false, 'index.UserID')
            ->column('RecordType', 'varchar(20)', false, 'index.RecordType')
            ->column('RecordID', 'int', false, 'index.RecordID')
            ->column('Score', 'tinyint', '0')
            ->column('DateInserted', 'datetime', false)
            ->set(false, false);
    }
}
