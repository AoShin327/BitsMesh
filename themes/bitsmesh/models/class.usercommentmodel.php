<?php
/**
 * UserComment Model
 *
 * Handles comment reactions (like/dislike) for BitsMesh theme.
 * Uses existing Score field in Comment table for aggregate counts.
 *
 * @package BitsMesh
 * @since 1.0
 */

/**
 * UserCommentModel - Manages user reactions to comments.
 *
 * Database Tables:
 * - GDN_UserComment: Stores individual user reactions (UserID, CommentID, Score)
 * - GDN_Comment: Uses Score field for aggregate like count
 *
 * Score Values:
 * - 1: Like
 * - -1: Dislike
 * - 0: Neutral (reaction removed)
 */
class UserCommentModel extends Gdn_Model {

    /** @var UserCommentModel Singleton instance */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('UserComment');
        $this->PrimaryKey = 'UserCommentID';
    }

    /**
     * Get singleton instance.
     *
     * @return UserCommentModel
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new UserCommentModel();
        }
        return self::$instance;
    }

    /**
     * Get user's reaction to a specific comment.
     *
     * @param int $userID User ID
     * @param int $commentID Comment ID
     * @return array|false User reaction record or false if not found
     */
    public function getUserReaction($userID, $commentID) {
        if (!$userID || !$commentID) {
            return false;
        }

        return $this->SQL
            ->select('*')
            ->from('UserComment')
            ->where('UserID', (int)$userID)
            ->where('CommentID', (int)$commentID)
            ->get()
            ->firstRow(DATASET_TYPE_ARRAY);
    }

    /**
     * Set user's reaction to a comment (like or dislike).
     *
     * This method handles:
     * 1. Creating new reaction
     * 2. Toggling off existing reaction (same score)
     * 3. Switching reaction (like to dislike or vice versa)
     *
     * @param int $userID User ID
     * @param int $commentID Comment ID
     * @param int $score Reaction score (1 = like, -1 = dislike)
     * @return array Result with 'success', 'action', 'newScore', 'likeCount', 'dislikeCount'
     */
    public function setReaction($userID, $commentID, $score) {
        // Validate inputs
        $userID = (int)$userID;
        $commentID = (int)$commentID;
        $score = (int)$score;

        if (!$userID) {
            return ['success' => false, 'error' => 'NotLoggedIn'];
        }

        if (!$commentID) {
            return ['success' => false, 'error' => 'InvalidComment'];
        }

        if (!in_array($score, [1, -1])) {
            return ['success' => false, 'error' => 'InvalidScore'];
        }

        // Get current reaction
        $existing = $this->getUserReaction($userID, $commentID);
        $action = 'added';

        if ($existing) {
            $oldScore = (int)$existing['Score'];

            if ($oldScore === $score) {
                // Same reaction: toggle off (remove)
                $this->SQL
                    ->delete('UserComment', [
                        'UserID' => $userID,
                        'CommentID' => $commentID
                    ]);
                $action = 'removed';
                $newScore = 0;
            } else {
                // Different reaction: switch
                $this->SQL
                    ->update('UserComment')
                    ->set('Score', $score)
                    ->set('DateInserted', Gdn_Format::toDateTime())
                    ->where('UserID', $userID)
                    ->where('CommentID', $commentID)
                    ->put();
                $action = 'switched';
                $newScore = $score;
            }
        } else {
            // New reaction
            $this->SQL->insert('UserComment', [
                'UserID' => $userID,
                'CommentID' => $commentID,
                'Score' => $score,
                'DateInserted' => Gdn_Format::toDateTime()
            ]);
            $newScore = $score;
        }

        // Update aggregate counts on Comment table
        $counts = $this->getCommentReactionCounts($commentID);

        // Update Comment.Score with net score (likes - dislikes)
        $netScore = $counts['likeCount'] - $counts['dislikeCount'];
        $this->SQL
            ->update('Comment')
            ->set('Score', $netScore)
            ->where('CommentID', $commentID)
            ->put();

        return [
            'success' => true,
            'action' => $action,
            'newScore' => $newScore,
            'likeCount' => $counts['likeCount'],
            'dislikeCount' => $counts['dislikeCount'],
            'netScore' => $netScore
        ];
    }

    /**
     * Get reaction counts for a comment.
     *
     * @param int $commentID Comment ID
     * @return array ['likeCount' => int, 'dislikeCount' => int]
     */
    public function getCommentReactionCounts($commentID) {
        $commentID = (int)$commentID;

        // Count likes
        $likeCount = $this->SQL
            ->select('UserCommentID', 'count', 'Count')
            ->from('UserComment')
            ->where('CommentID', $commentID)
            ->where('Score', 1)
            ->get()
            ->firstRow();
        $likeCount = $likeCount ? (int)$likeCount->Count : 0;

        // Count dislikes
        $dislikeCount = $this->SQL
            ->select('UserCommentID', 'count', 'Count')
            ->from('UserComment')
            ->where('CommentID', $commentID)
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
     * Get user's reactions for multiple comments.
     *
     * Optimized for loading reactions when viewing a discussion.
     *
     * @param int $userID User ID
     * @param array $commentIDs Array of comment IDs
     * @return array Map of CommentID => Score
     */
    public function getUserReactions($userID, array $commentIDs) {
        if (!$userID || empty($commentIDs)) {
            return [];
        }

        $reactions = $this->SQL
            ->select('CommentID, Score')
            ->from('UserComment')
            ->where('UserID', (int)$userID)
            ->whereIn('CommentID', array_map('intval', $commentIDs))
            ->get()
            ->resultArray();

        $result = [];
        foreach ($reactions as $reaction) {
            $result[(int)$reaction['CommentID']] = (int)$reaction['Score'];
        }

        return $result;
    }

    /**
     * Get reaction counts for multiple comments.
     *
     * Optimized for loading counts when viewing a discussion.
     *
     * @param array $commentIDs Array of comment IDs
     * @return array Map of CommentID => ['likeCount' => int, 'dislikeCount' => int]
     */
    public function getReactionCounts(array $commentIDs) {
        if (empty($commentIDs)) {
            return [];
        }

        $commentIDs = array_map('intval', $commentIDs);

        // Get all reactions for these comments
        $reactions = $this->SQL
            ->select('CommentID, Score, COUNT(*) as Count')
            ->from('UserComment')
            ->whereIn('CommentID', $commentIDs)
            ->groupBy('CommentID, Score')
            ->get()
            ->resultArray();

        // Build result map
        $result = [];
        foreach ($commentIDs as $id) {
            $result[$id] = ['likeCount' => 0, 'dislikeCount' => 0];
        }

        foreach ($reactions as $reaction) {
            $commentID = (int)$reaction['CommentID'];
            $score = (int)$reaction['Score'];
            $count = (int)$reaction['Count'];

            if ($score === 1) {
                $result[$commentID]['likeCount'] = $count;
            } elseif ($score === -1) {
                $result[$commentID]['dislikeCount'] = $count;
            }
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

        $construct->table('UserComment')
            ->primaryKey('UserCommentID')
            ->column('UserID', 'int', false, 'index.UserID')
            ->column('CommentID', 'int', false, 'index.CommentID')
            ->column('Score', 'tinyint', '0')
            ->column('DateInserted', 'datetime', false)
            ->set(false, false);
    }
}
