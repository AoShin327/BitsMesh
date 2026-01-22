<?php
/**
 * User Follow Model
 *
 * Handles user follow/unfollow relationships.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

/**
 * Class UserFollowModel
 *
 * Manages user follow relationships with caching support.
 */
class UserFollowModel extends Gdn_Model {

    /** Cache key prefix for follow counts */
    const CACHE_KEY_FOLLOWING = 'userfollow.following.%d';
    const CACHE_KEY_FOLLOWERS = 'userfollow.followers.%d';
    const CACHE_KEY_IS_FOLLOWING = 'userfollow.isfollowing.%d.%d';

    /** Cache expiry in seconds (1 hour) */
    const CACHE_EXPIRY = 3600;

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct('UserFollow');
        $this->PrimaryKey = 'FollowID';
    }

    /**
     * Ensure database table exists.
     *
     * Called from ThemeHooks::structure()
     *
     * @return void
     */
    public static function structure() {
        $construct = Gdn::structure();

        $construct->table('UserFollow')
            ->primaryKey('FollowID')
            ->column('UserID', 'int', false, 'index.user')
            ->column('FollowUserID', 'int', false, 'index.followuser')
            ->column('DateInserted', 'datetime', false)
            ->set(false, false);

        // Add unique constraint to prevent duplicate follows
        // We use a unique index on (UserID, FollowUserID)
        $px = Gdn::database()->DatabasePrefix;
        $indexName = 'UX_UserFollow_User_FollowUser';

        // Check if index exists
        $indexExists = false;
        try {
            $indexes = Gdn::sql()->query("SHOW INDEX FROM {$px}UserFollow WHERE Key_name = '{$indexName}'")->resultArray();
            $indexExists = count($indexes) > 0;
        } catch (Exception $e) {
            // Table might not exist yet
        }

        if (!$indexExists) {
            try {
                Gdn::sql()->query("CREATE UNIQUE INDEX {$indexName} ON {$px}UserFollow (UserID, FollowUserID)");
            } catch (Exception $e) {
                // Index might already exist or table doesn't exist
            }
        }
    }

    /**
     * Follow a user.
     *
     * @param int $userID The user who is following.
     * @param int $followUserID The user to follow.
     * @return bool|int FollowID on success, false on failure.
     */
    public function follow($userID, $followUserID) {
        $userID = (int)$userID;
        $followUserID = (int)$followUserID;

        // Validate inputs
        if ($userID <= 0 || $followUserID <= 0) {
            return false;
        }

        // Cannot follow yourself
        if ($userID === $followUserID) {
            return false;
        }

        // Check if target user exists
        $userModel = new UserModel();
        $targetUser = $userModel->getID($followUserID);
        if (!$targetUser) {
            return false;
        }

        // Check if already following
        if ($this->isFollowing($userID, $followUserID)) {
            return true; // Already following, return success
        }

        // Insert follow relationship
        try {
            $followID = $this->SQL->insert($this->Name, [
                'UserID' => $userID,
                'FollowUserID' => $followUserID,
                'DateInserted' => Gdn_Format::toDateTime()
            ]);

            // Clear cache
            $this->clearCache($userID, $followUserID);

            return $followID;
        } catch (Exception $e) {
            // Likely a duplicate key error, which is fine
            return $this->isFollowing($userID, $followUserID);
        }
    }

    /**
     * Unfollow a user.
     *
     * @param int $userID The user who is unfollowing.
     * @param int $followUserID The user to unfollow.
     * @return bool True on success.
     */
    public function unfollow($userID, $followUserID) {
        $userID = (int)$userID;
        $followUserID = (int)$followUserID;

        if ($userID <= 0 || $followUserID <= 0) {
            return false;
        }

        $this->SQL->delete($this->Name, [
            'UserID' => $userID,
            'FollowUserID' => $followUserID
        ]);

        // Clear cache
        $this->clearCache($userID, $followUserID);

        return true;
    }

    /**
     * Toggle follow status.
     *
     * @param int $userID The user who is toggling.
     * @param int $followUserID The user to toggle follow for.
     * @return array ['isFollowing' => bool, 'followingCount' => int, 'followersCount' => int]
     */
    public function toggle($userID, $followUserID) {
        $userID = (int)$userID;
        $followUserID = (int)$followUserID;

        if ($this->isFollowing($userID, $followUserID)) {
            $this->unfollow($userID, $followUserID);
            $isFollowing = false;
        } else {
            $this->follow($userID, $followUserID);
            $isFollowing = true;
        }

        return [
            'isFollowing' => $isFollowing,
            'followingCount' => $this->getFollowingCount($userID),
            'followersCount' => $this->getFollowersCount($followUserID)
        ];
    }

    /**
     * Check if user A is following user B.
     *
     * @param int $userID User A.
     * @param int $followUserID User B.
     * @return bool
     */
    public function isFollowing($userID, $followUserID) {
        $userID = (int)$userID;
        $followUserID = (int)$followUserID;

        if ($userID <= 0 || $followUserID <= 0) {
            return false;
        }

        // Check cache first
        $cacheKey = sprintf(self::CACHE_KEY_IS_FOLLOWING, $userID, $followUserID);
        $cached = Gdn::cache()->get($cacheKey);
        if ($cached !== Gdn_Cache::CACHEOP_FAILURE) {
            return (bool)$cached;
        }

        $count = $this->SQL
            ->select('FollowID', 'count', 'Count')
            ->from($this->Name)
            ->where('UserID', $userID)
            ->where('FollowUserID', $followUserID)
            ->get()
            ->firstRow()
            ->Count;

        $isFollowing = $count > 0;

        // Cache result
        Gdn::cache()->store($cacheKey, $isFollowing ? 1 : 0, [
            Gdn_Cache::FEATURE_EXPIRY => self::CACHE_EXPIRY
        ]);

        return $isFollowing;
    }

    /**
     * Get count of users that a user is following.
     *
     * @param int $userID User ID.
     * @return int
     */
    public function getFollowingCount($userID) {
        $userID = (int)$userID;

        if ($userID <= 0) {
            return 0;
        }

        // Check cache
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWING, $userID);
        $cached = Gdn::cache()->get($cacheKey);
        if ($cached !== Gdn_Cache::CACHEOP_FAILURE) {
            return (int)$cached;
        }

        $count = $this->SQL
            ->select('FollowID', 'count', 'Count')
            ->from($this->Name)
            ->where('UserID', $userID)
            ->get()
            ->firstRow()
            ->Count;

        // Cache result
        Gdn::cache()->store($cacheKey, $count, [
            Gdn_Cache::FEATURE_EXPIRY => self::CACHE_EXPIRY
        ]);

        return (int)$count;
    }

    /**
     * Get count of followers for a user.
     *
     * @param int $userID User ID.
     * @return int
     */
    public function getFollowersCount($userID) {
        $userID = (int)$userID;

        if ($userID <= 0) {
            return 0;
        }

        // Check cache
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWERS, $userID);
        $cached = Gdn::cache()->get($cacheKey);
        if ($cached !== Gdn_Cache::CACHEOP_FAILURE) {
            return (int)$cached;
        }

        $count = $this->SQL
            ->select('FollowID', 'count', 'Count')
            ->from($this->Name)
            ->where('FollowUserID', $userID)
            ->get()
            ->firstRow()
            ->Count;

        // Cache result
        Gdn::cache()->store($cacheKey, $count, [
            Gdn_Cache::FEATURE_EXPIRY => self::CACHE_EXPIRY
        ]);

        return (int)$count;
    }

    /**
     * Get list of users that a user is following.
     *
     * @param int $userID User ID.
     * @param int $limit Number of results.
     * @param int $offset Offset.
     * @return Gdn_DataSet
     */
    public function getFollowing($userID, $limit = 20, $offset = 0) {
        $userID = (int)$userID;

        if ($userID <= 0) {
            return new Gdn_DataSet([]);
        }

        return $this->SQL
            ->select('u.UserID, u.Name, u.Photo, u.DateInserted as UserDateInserted, uf.DateInserted as FollowDate')
            ->from($this->Name . ' uf')
            ->join('User u', 'uf.FollowUserID = u.UserID')
            ->where('uf.UserID', $userID)
            ->where('u.Deleted', 0)
            ->orderBy('uf.DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get();
    }

    /**
     * Get list of followers for a user.
     *
     * @param int $userID User ID.
     * @param int $limit Number of results.
     * @param int $offset Offset.
     * @return Gdn_DataSet
     */
    public function getFollowers($userID, $limit = 20, $offset = 0) {
        $userID = (int)$userID;

        if ($userID <= 0) {
            return new Gdn_DataSet([]);
        }

        return $this->SQL
            ->select('u.UserID, u.Name, u.Photo, u.DateInserted as UserDateInserted, uf.DateInserted as FollowDate')
            ->from($this->Name . ' uf')
            ->join('User u', 'uf.UserID = u.UserID')
            ->where('uf.FollowUserID', $userID)
            ->where('u.Deleted', 0)
            ->orderBy('uf.DateInserted', 'desc')
            ->limit($limit, $offset)
            ->get();
    }

    /**
     * Check if two users follow each other (mutual follow).
     *
     * @param int $userA First user.
     * @param int $userB Second user.
     * @return bool
     */
    public function isMutualFollow($userA, $userB) {
        return $this->isFollowing($userA, $userB) && $this->isFollowing($userB, $userA);
    }

    /**
     * Clear cached data for user relationships.
     *
     * @param int $userID User who followed/unfollowed.
     * @param int $followUserID User who was followed/unfollowed.
     * @return void
     */
    private function clearCache($userID, $followUserID) {
        $cache = Gdn::cache();

        // Clear following count for user who followed
        $cache->remove(sprintf(self::CACHE_KEY_FOLLOWING, $userID));

        // Clear followers count for user who was followed
        $cache->remove(sprintf(self::CACHE_KEY_FOLLOWERS, $followUserID));

        // Clear isFollowing cache
        $cache->remove(sprintf(self::CACHE_KEY_IS_FOLLOWING, $userID, $followUserID));
    }

    /**
     * Get singleton instance.
     *
     * @return UserFollowModel
     */
    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new UserFollowModel();
        }
        return $instance;
    }
}
