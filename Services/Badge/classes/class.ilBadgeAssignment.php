<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBadgeAssignment
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeAssignment
{
    protected ilDBInterface $db;
    protected int $badge_id = 0;
    protected int $user_id = 0;
    protected int $tstamp = 0; // unix timestamp
    protected int $awarded_by = 0;
    protected ?int $pos = null;
    protected bool $stored = false;
    
    public function __construct(
        int $a_badge_id = null,
        int $a_user_id = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_badge_id &&
            $a_user_id) {
            $this->setBadgeId($a_badge_id);
            $this->setUserId($a_user_id);
            
            $this->read($a_badge_id, $a_user_id);
        }
    }

    public static function getNewCounter(
        int $a_user_id
    ) : int {
        global $DIC;

        $db = $DIC->database();

        $user = new ilObjUser($a_user_id);
        $noti_repo = new \ILIAS\Badge\Notification\BadgeNotificationPrefRepository($user);

        $last = $noti_repo->getLastCheckedTimestamp();


        // if no last check exists, we use last 24 hours
        if ($last === 0) {
            $last = time() - (24 * 60 * 60);
        }

        if ($last > 0) {
            $set = $db->queryF(
                "SELECT count(*) cnt FROM badge_user_badge " .
                " WHERE user_id = %s AND tstamp >= %s",
                ["integer", "integer"],
                [$a_user_id, $last]
            );
            $rec = $db->fetchAssoc($set);
            return (int) $rec["cnt"];
        }
        return 0;
    }

    public static function getLatestTimestamp(
        int $a_user_id
    ) : int {
        global $DIC;

        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT max(tstamp) maxts FROM badge_user_badge " .
            " WHERE user_id = %s",
            ["integer"],
            [$a_user_id]
        );
        $rec = $db->fetchAssoc($set);
        return (int) $rec["maxts"];
    }

    /**
     * @param int $a_user_id
     * @return self[]
     */
    public static function getInstancesByUserId(
        int $a_user_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();

        $set = $ilDB->query("SELECT * FROM badge_user_badge" .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " ORDER BY pos");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }
        
        return $res;
    }

    /**
     * @param int $a_badge_id
     * @return self[]
     */
    public static function getInstancesByBadgeId(
        int $a_badge_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $set = $ilDB->query("SELECT * FROM badge_user_badge" .
            " WHERE badge_id = " . $ilDB->quote($a_badge_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }
        
        return $res;
    }

    /**
     * @param int $a_parent_obj_id
     * @return self[]
     */
    public static function getInstancesByParentId(
        int $a_parent_obj_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $badge_ids = array();
        foreach (ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge) {
            $badge_ids[] = $badge->getId();
        }
        if (count($badge_ids)) {
            $set = $ilDB->query("SELECT * FROM badge_user_badge" .
            " WHERE " . $ilDB->in("badge_id", $badge_ids, "", "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $obj = new self();
                $obj->importDBRow($row);
                $res[] = $obj;
            }
        }
        
        return $res;
    }

    /**
     * @param int $a_badge_id
     * @return int[]
     */
    public static function getAssignedUsers(
        int $a_badge_id
    ) : array {
        $res = [];
        
        foreach (self::getInstancesByBadgeId($a_badge_id) as $ass) {
            $res[] = $ass->getUserId();
        }
        
        return $res;
    }
    
    public static function exists(
        int $a_badge_id,
        int $a_user_id
    ) : bool {
        $obj = new self($a_badge_id, $a_user_id);
        return $obj->stored;
    }
    
    
    //
    // setter/getter
    //
    
    protected function setBadgeId(int $a_value) : void
    {
        $this->badge_id = $a_value;
    }
    
    public function getBadgeId() : int
    {
        return $this->badge_id;
    }
    
    protected function setUserId(int $a_value) : void
    {
        $this->user_id = $a_value;
    }
    
    public function getUserId() : int
    {
        return $this->user_id;
    }
    
    protected function setTimestamp(int $a_value) : void
    {
        $this->tstamp = $a_value;
    }
    
    public function getTimestamp() : int
    {
        return $this->tstamp;
    }
    
    public function setAwardedBy(int $a_id) : void
    {
        $this->awarded_by = $a_id;
    }
    
    public function getAwardedBy() : int
    {
        return $this->awarded_by;
    }
    
    public function setPosition(?int $a_value) : void
    {
        $this->pos = $a_value;
    }
    
    public function getPosition() : ?int
    {
        return $this->pos;
    }
    
    
    //
    // crud
    //
    
    protected function importDBRow(array $a_row) : void
    {
        $this->stored = true;
        $this->setBadgeId((int) $a_row["badge_id"]);
        $this->setUserId((int) $a_row["user_id"]);
        $this->setTimestamp((int) $a_row["tstamp"]);
        $this->setAwardedBy((int) $a_row["awarded_by"]);
        $this->setPosition($a_row["pos"]);
    }
    
    protected function read(
        int $a_badge_id,
        int $a_user_id
    ) : void {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM badge_user_badge" .
            " WHERE badge_id = " . $ilDB->quote($a_badge_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row && $row["user_id"]) {
            $this->importDBRow($row);
        }
    }

    /**
     * @return array<string, array>
     */
    protected function getPropertiesForStorage() : array
    {
        return [
            "tstamp" => ["integer", $this->stored ? $this->getTimestamp() : time()],
            "awarded_by" => ["integer", $this->getAwardedBy()],
            "pos" => ["integer", $this->getPosition()]
        ];
    }
    
    public function store() : void
    {
        $ilDB = $this->db;
        
        if (!$this->getBadgeId() ||
            !$this->getUserId()) {
            return;
        }
        
        $keys = array(
            "badge_id" => array("integer", $this->getBadgeId()),
            "user_id" => array("integer", $this->getUserId())
        );
        $fields = $this->getPropertiesForStorage();
        
        if (!$this->stored) {
            $ilDB->insert("badge_user_badge", $fields + $keys);
        } else {
            $ilDB->update("badge_user_badge", $fields, $keys);
        }
    }
    
    public function delete() : void
    {
        $ilDB = $this->db;
        
        if (!$this->getBadgeId() ||
            !$this->getUserId()) {
            return;
        }
        
        $this->deleteStaticFiles();
        
        $ilDB->manipulate("DELETE FROM badge_user_badge" .
            " WHERE badge_id = " . $ilDB->quote($this->getBadgeId(), "integer") .
            " AND user_id = " . $ilDB->quote($this->getUserId(), "integer"));
    }
    
    public static function deleteByUserId(int $a_user_id) : void
    {
        foreach (self::getInstancesByUserId($a_user_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function deleteByBadgeId(int $a_badge_id) : void
    {
        foreach (self::getInstancesByBadgeId($a_badge_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function deleteByParentId(int $a_parent_obj_id) : void
    {
        foreach (self::getInstancesByParentId($a_parent_obj_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function updatePositions(
        int $a_user_id,
        array $a_positions
    ) : void {
        $existing = array();
        foreach (self::getInstancesByUserId($a_user_id) as $ass) {
            $badge = new ilBadge($ass->getBadgeId());
            $existing[$badge->getId()] = array($badge->getTitle(), $ass);
        }
        
        $new_pos = 0;
        foreach ($a_positions as $title) {
            foreach ($existing as $id => $item) {
                if ($title == $item[0]) {
                    $item[1]->setPosition(++$new_pos);
                    $item[1]->store();
                    unset($existing[$id]);
                }
            }
        }
    }

    /**
     * @param int $a_user_id
     * @param int $a_ts_from
     * @param int $a_ts_to
     * @return array[]
     */
    public static function getBadgesForUser(
        int $a_user_id,
        int $a_ts_from,
        int $a_ts_to
    ) : array {
        global $DIC;
        
        $db = $DIC->database();
        
        $set = $db->queryF(
            "SELECT bdg.parent_id, ub.tstamp, bdg.title FROM badge_user_badge ub JOIN badge_badge bdg" .
            " ON (ub.badge_id = bdg.id) " .
            " WHERE ub.user_id = %s AND ub.tstamp >= %s AND ub.tstamp <= %s",
            array("integer","integer","integer"),
            array($a_user_id, $a_ts_from, $a_ts_to)
        );
        $res = [];
        while ($rec = $db->fetchAssoc($set)) {
            $res[] = $rec;
        }
        return $res;
    }

    public function deleteStaticFiles() : void
    {
        // remove instance files
        $path = ilBadgeHandler::getInstance()->getInstancePath($this);
        $path = str_replace(".json", ".*", $path);
        array_map("unlink", glob($path));
    }
        
    public static function clearBadgeCache(
        int $a_user_id
    ) : void {
        foreach (self::getInstancesByUserId($a_user_id) as $ass) {
            $ass->deleteStaticFiles();
        }
    }
}
