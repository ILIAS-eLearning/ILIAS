<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeAssignment
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeAssignment
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $badge_id; // [int]
    protected $user_id; // [int]
    protected $tstamp; // [timestamp]
    protected $awarded_by; // [int]
    protected $pos; // [int]
    protected $stored; // [bool]
    
    public function __construct($a_badge_id = null, $a_user_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_badge_id &&
            $a_user_id) {
            $this->setBadgeId($a_badge_id);
            $this->setUserId($a_user_id);
            
            $this->read($a_badge_id, $a_user_id);
        }
    }
    
    public static function getInstancesByUserId($a_user_id)
    {
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
    
    public static function getInstancesByBadgeId($a_badge_id)
    {
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
    
    public static function getInstancesByParentId($a_parent_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $badge_ids = array();
        foreach (ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge) {
            $badge_ids[] = $badge->getId();
        }
        if (sizeof($badge_ids)) {
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
    
    public static function getAssignedUsers($a_badge_id)
    {
        $res = array();
        
        foreach (self::getInstancesByBadgeId($a_badge_id) as $ass) {
            $res[] = $ass->getUserId();
        }
        
        return $res;
    }
    
    public static function exists($a_badge_id, $a_user_id)
    {
        $obj = new self($a_badge_id, $a_user_id);
        return $obj->stored;
    }
    
    
    //
    // setter/getter
    //
    
    protected function setBadgeId($a_value)
    {
        $this->badge_id = (int) $a_value;
    }
    
    public function getBadgeId()
    {
        return $this->badge_id;
    }
    
    protected function setUserId($a_value)
    {
        $this->user_id = (int) $a_value;
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    protected function setTimestamp($a_value)
    {
        $this->tstamp = (int) $a_value;
    }
    
    public function getTimestamp()
    {
        return $this->tstamp;
    }
    
    public function setAwardedBy($a_id)
    {
        $this->awarded_by = (int) $a_id;
    }
    
    public function getAwardedBy()
    {
        return $this->awarded_by;
    }
    
    public function setPosition($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->pos = $a_value;
    }
    
    public function getPosition()
    {
        return $this->pos;
    }
    
    
    //
    // crud
    //
    
    protected function importDBRow(array $a_row)
    {
        $this->stored = true;
        $this->setBadgeId($a_row["badge_id"]);
        $this->setUserId($a_row["user_id"]);
        $this->setTimestamp($a_row["tstamp"]);
        $this->setAwardedBy($a_row["awarded_by"]);
        $this->setPosition($a_row["pos"]);
    }
    
    protected function read($a_badge_id, $a_user_id)
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM badge_user_badge" .
            " WHERE badge_id = " . $ilDB->quote($a_badge_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row["user_id"]) {
            $this->importDBRow($row);
        }
    }
    
    protected function getPropertiesForStorage()
    {
        return array(
            "tstamp" => array("integer", (bool) $this->stored ? $this->getTimestamp() : time()),
            "awarded_by" => array("integer", $this->getAwardedBy()),
            "pos" => array("integer", $this->getPosition())
        );
    }
    
    public function store()
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
        
        if (!(bool) $this->stored) {
            $ilDB->insert("badge_user_badge", $fields + $keys);
        } else {
            $ilDB->update("badge_user_badge", $fields, $keys);
        }
    }
    
    public function delete()
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
    
    public static function deleteByUserId($a_user_id)
    {
        foreach (self::getInstancesByUserId($a_user_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function deleteByBadgeId($a_badge_id)
    {
        foreach (self::getInstancesByBadgeId($a_badge_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function deleteByParentId($a_parent_obj_id)
    {
        foreach (self::getInstancesByParentId($a_parent_obj_id) as $ass) {
            $ass->delete();
        }
    }
    
    public static function updatePositions($a_user_id, array $a_positions)
    {
        $existing = array();
        include_once "Services/Badge/classes/class.ilBadge.php";
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
     * Get badges for user
     * @param int $a_user_id
     * @param int $a_ts_from
     * @param int $a_ts_to
     * @return array
     */
    public static function getBadgesForUser($a_user_id, $a_ts_from, $a_ts_to)
    {
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
    
    
    //
    // PUBLISHING
    //
    
    protected function prepareJson($a_url)
    {
        $verify = new stdClass();
        $verify->type = "hosted";
        $verify->url = $a_url;
        
        $recipient = new stdClass();
        $recipient->type = "email";
        $recipient->hashed = true;
        $recipient->salt = ilBadgeHandler::getInstance()->getObiSalt();
                        
        // https://github.com/mozilla/openbadges-backpack/wiki/How-to-hash-&-salt-in-various-languages.
        include_once "Services/Badge/classes/class.ilBadgeProfileGUI.php";
        $user = new ilObjUser($this->getUserId());
        $mail = $user->getPref(ilBadgeProfileGUI::BACKPACK_EMAIL);
        if (!$mail) {
            $mail = $user->getEmail();
        }
        $recipient->identity = 'sha256$' . hash('sha256', $mail . $recipient->salt);
        
        // spec: should be locally unique
        $unique_id = md5($this->getBadgeId() . "-" . $this->getUserId());
        
        $json = new stdClass();
        $json->{"@context"} = "https://w3id.org/openbadges/v1";
        $json->type = "Assertion";
        $json->id = $a_url;
        $json->uid = $unique_id;
        $json->recipient = $recipient;
            
        include_once "Services/Badge/classes/class.ilBadge.php";
        $badge = new ilBadge($this->getBadgeId());
        $badge_url = $badge->getStaticUrl();
                                    
        // created baked image
        $baked_image = $this->getImagePath($badge);
        if ($this->bakeImage($baked_image, $badge->getImagePath(), $a_url)) {
            // path to url
            $parts = explode("/", $a_url);
            array_pop($parts);
            $parts[] = basename($baked_image);
            $json->image = implode("/", $parts);
        }
        
        $json->issuedOn = $this->getTimestamp();
        $json->badge = $badge_url;
        $json->verify = $verify;
        
        return $json;
    }
    
    public function getImagePath(ilBadge $a_badge)
    {
        $json_path = ilBadgeHandler::getInstance()->getInstancePath($this);
        $baked_path = dirname($json_path);
        $baked_file = array_shift(explode(".", basename($json_path)));
        
        // get correct suffix from badge image
        $suffix = strtolower(array_pop(explode(".", basename($a_badge->getImagePath()))));
        return $baked_path . "/" . $baked_file . "." . $suffix;
    }
    
    protected function bakeImage($a_baked_image_path, $a_badge_image_path, $a_assertion_url)
    {
        $suffix = strtolower(array_pop(explode(".", basename($a_badge_image_path))));
        if ($suffix == "png") {
            // using chamilo baker lib
            include_once "Services/Badge/lib/baker.lib.php";
            $png = new PNGImageBaker(file_get_contents($a_badge_image_path));
            
            // add payload
            if ($png->checkChunks("tEXt", "openbadges")) {
                $baked = $png->addChunk("tEXt", "openbadges", $a_assertion_url);
            }
            
            // create baked file
            if (!file_exists($a_baked_image_path)) {
                file_put_contents($a_baked_image_path, $baked);
            }
            
            // verify file
            $verify = $png->extractBadgeInfo(file_get_contents($a_baked_image_path));
            if (is_array($verify)) {
                return true;
            }
        } elseif ($suffix == "svg") {
            // :TODO: not really sure if this is correct
            $svg = simplexml_load_file($a_badge_image_path);
            $ass = $svg->addChild("openbadges:assertion", "", "http://openbadges.org");
            $ass->addAttribute("verify", $a_assertion_url);
            $baked = $svg->asXML();
            
            // create baked file
            if (!file_exists($a_baked_image_path)) {
                file_put_contents($a_baked_image_path, $baked);
            }
            
            return true;
        }
        
        return false;
    }
    
    public function getStaticUrl()
    {
        include_once("./Services/Badge/classes/class.ilBadgeHandler.php");
        $path = ilBadgeHandler::getInstance()->getInstancePath($this);
        
        $url = ILIAS_HTTP_PATH . substr($path, 1);
        
        if (!file_exists($path)) {
            $json = json_encode($this->prepareJson($url));
            file_put_contents($path, $json);
        }
        
        return $url;
    }
    
    public function deleteStaticFiles()
    {
        // remove instance files
        include_once("./Services/Badge/classes/class.ilBadgeHandler.php");
        $path = ilBadgeHandler::getInstance()->getInstancePath($this);
        $path = str_replace(".json", ".*", $path);
        array_map("unlink", glob($path));
    }
        
    public static function clearBadgeCache($a_user_id)
    {
        foreach (self::getInstancesByUserId($a_user_id) as $ass) {
            $ass->deleteStaticFiles();
        }
    }
}
