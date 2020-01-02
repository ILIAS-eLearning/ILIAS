<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesRating Services/Rating
 */

/**
* Class ilRating
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRating
*/
class ilRating
{
    protected static $list_data; // [array]
    
    /**
    * Write rating for a user and an object.
    *
    * @param	int			$a_obj_id			Object ID
    * @param	string		$a_obj_type			Object Type
    * @param	int			$a_sub_obj_id		Subobject ID
    * @param	string		$a_sub_obj_type		Subobject Type
    * @param	int			$a_user_id			User ID
    * @param	int			$a_rating			Rating
    * @param	int			$a_category_id		Category ID
    */
    public static function writeRatingForUserAndObject(
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id,
        $a_sub_obj_type,
        $a_user_id,
        $a_rating,
        $a_category_id = 0
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_user_id == ANONYMOUS_USER_ID) {
            return;
        }
        
        if ($a_category_id) {
            $ilDB->manipulate("DELETE FROM il_rating WHERE " .
                "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
                "obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") . " AND " .
                "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
                "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
                $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true) . " AND " .
                "category_id = " . $ilDB->quote(0, "integer"));
        }
        
        $ilDB->manipulate("DELETE FROM il_rating WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true) . " AND " .
            "category_id = " . $ilDB->quote((int) $a_category_id, "integer"));
        
        if ((int) $a_rating) {
            $ilDB->manipulate("INSERT INTO il_rating (user_id, obj_id, obj_type," .
                "sub_obj_id, sub_obj_type, category_id, rating, tstamp) VALUES (" .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote((int) $a_obj_id, "integer") . "," .
                $ilDB->quote($a_obj_type, "text") . "," .
                $ilDB->quote((int) $a_sub_obj_id, "integer") . "," .
                $ilDB->quote($a_sub_obj_type, "text") . "," .
                $ilDB->quote($a_category_id, "integer") . "," .
                $ilDB->quote((int) $a_rating, "integer") . "," .
                $ilDB->quote(time(), "integer") . ")");
        }
    }
            
    /**
    * Reset rating for a user and an object.
    *
    * @param	int			$a_obj_id			Object ID
    * @param	string		$a_obj_type			Object Type
    * @param	int			$a_sub_obj_id		Subobject ID
    * @param	string		$a_sub_obj_type		Subobject Type
    * @param	int			$a_user_id			User ID
    */
    public static function resetRatingForUserAndObject(
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id,
        $a_sub_obj_type,
        $a_user_id
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate("DELETE FROM il_rating WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true));
    }
    
    
    /**
    * Get rating for a user and an object.
    *
    * @param	int			$a_obj_id			Object ID
    * @param	string		$a_obj_type			Object Type
    * @param	int			$a_sub_obj_id		Subobject ID
    * @param	string		$a_sub_obj_type		Subobject Type
    * @param	int			$a_user_id			User ID
    * @param	int			$a_category_id		Category ID
    */
    public static function getRatingForUserAndObject(
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id,
        $a_sub_obj_type,
        $a_user_id,
        $a_category_id = null
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (is_array(self::$list_data)) {
            return self::$list_data["user"][$a_obj_type . "/" . $a_obj_id];
        }
        
        $q = "SELECT AVG(rating) av FROM il_rating WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true);
        if ($a_category_id !== null) {
            $q .= " AND category_id = " . $ilDB->quote((int) $a_category_id, "integer");
        }
        $set = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($set);
        return $rec["av"];
    }
    
    /**
    * Get overall rating for an object.
    *
    * @param	int			$a_obj_id			Object ID
    * @param	string		$a_obj_type			Object Type
    * @param	int			$a_sub_obj_id		Subobject ID
    * @param	string		$a_sub_obj_type		Subobject Type
    * @param	int			$a_category_id		Category ID
    */
    public static function getOverallRatingForObject($a_obj_id, $a_obj_type, $a_sub_obj_id = null, $a_sub_obj_type = null, $a_category_id = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
    
        if (is_array(self::$list_data)) {
            return self::$list_data["all"][$a_obj_type . "/" . $a_obj_id];
        }
        
        $q = "SELECT AVG(rating) av FROM il_rating" .
            " WHERE obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text");
        if ($a_sub_obj_id) {
            $q .= " AND sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") .
                " AND " . $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true);
        } else {
            $q .= " AND sub_obj_type = " . $ilDB->quote("-", "text"); // #13913
        }
        
        if ($a_category_id !== null) {
            $q .= " AND category_id = " . $ilDB->quote((int) $a_category_id, "integer");
        }
        $q .= " GROUP BY user_id";
        $set = $ilDB->query($q);
        $avg = $cnt = 0;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cnt++;
            $avg += $rec["av"];
        }
        if ($cnt > 0) {
            $avg = $avg/$cnt;
        } else {
            $avg = 0;
        }
        return array("cnt" => $cnt, "avg" => $avg);
    }

    /**
     * Get export data
     *
     * @param int $a_obj_id
     * @param string $a_obj_type
     * @param array $a_category_ids
     * @return array
     */
    public static function getExportData($a_obj_id, $a_obj_type, array $a_category_ids = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        $q = "SELECT sub_obj_id, sub_obj_type, rating, category_id, user_id, tstamp " .
            "FROM il_rating WHERE " .
            "obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") .
            " ORDER BY tstamp";
        if ($a_category_ids) {
            $q .= " AND " . $ilDB->in("category_id", $a_category_ids, "", "integer");
        }
        $set = $ilDB->query($q);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }
    
    /**
     * Preload rating data for list guis
     *
     * @param array $a_obj_ids
     */
    public static function preloadListGUIData(array $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $tmp = $res = $tmp_user = $res_user = array();
        
        // collapse by categories
        $q = "SELECT obj_id, obj_type, user_id, AVG(rating) av" .
            " FROM il_rating" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer") .
            " AND sub_obj_id = " . $ilDB->quote(0, "integer") .
            " GROUP BY obj_id, obj_type, user_id";
        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $tmp[$rec["obj_type"] . "/" . $rec["obj_id"]][$rec["user_id"]] = (float) $rec["av"];
            if ($rec["user_id"] == $ilUser->getId()) {
                // add final average to user result (no sub-objects)
                $res_user[$rec["obj_type"] . "/" . $rec["obj_id"]] = (float) $rec["av"];
            }
        }
        
        // average for main objects without sub-objects
        foreach ($tmp as $obj_id => $votes) {
            $res[$obj_id] = array("avg"=>array_sum($votes)/sizeof($votes),
                "cnt"=>sizeof($votes));
        }
        
        // file/wiki/lm rating toggles
        
        $set = $ilDB->query("SELECT file_id, rating" .
            " FROM file_data" .
            " WHERE " . $ilDB->in("file_id", $a_obj_ids, "", 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = "file/" . $row["file_id"];
            if ($row["rating"] && !isset($res[$id])) {
                $res[$id] = array("avg"=>0, "cnt"=>0);
            } elseif (!$row["rating"] && isset($res[$id])) {
                unset($res[$id]);
            }
        }
        
        $set = $ilDB->query("SELECT id, rating_overall" .
            " FROM il_wiki_data" .
            " WHERE " . $ilDB->in("id", $a_obj_ids, "", 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = "wiki/" . $row["id"];
            if ($row["rating_overall"] && !isset($res[$id])) {
                $res[$id] = array("avg"=>0, "cnt"=>0);
            } elseif (!$row["rating_overall"] && isset($res[$id])) {
                unset($res[$id]);
            }
        }
        
        $set = $ilDB->query("SELECT id, rating" .
            " FROM content_object" .
            " WHERE " . $ilDB->in("id", $a_obj_ids, "", 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = "lm/" . $row["id"];
            if ($row["rating"] && !isset($res[$id])) {
                $res[$id] = array("avg"=>0, "cnt"=>0);
            } elseif (!$row["rating"] && isset($res[$id])) {
                unset($res[$id]);
            }
        }
        
        self::$list_data = array("all"=>$res, "user"=>$res_user);
    }
    
    public static function hasRatingInListGUI($a_obj_id, $a_obj_type)
    {
        return isset(self::$list_data["all"][$a_obj_type . "/" . $a_obj_id]);
    }
}
