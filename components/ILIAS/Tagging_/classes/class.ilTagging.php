<?php

declare(strict_types=1);

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
 * Class ilTagging
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTagging
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    // Write tags for a user and an object.
    public static function writeTagsForUserAndObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type,
        int $a_user_id,
        array $a_tags
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->manipulate("DELETE FROM il_tag WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            // PHP8 Review: Type cast is unnecessary
            "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true));

        if (is_array($a_tags)) {
            $inserted = array();
            foreach ($a_tags as $tag) {
                if (!in_array(strtolower($tag), $inserted)) {
                    $ilDB->manipulate("INSERT INTO il_tag (user_id, obj_id, obj_type," .
                        "sub_obj_id, sub_obj_type, tag) VALUES (" .
                        $ilDB->quote($a_user_id, "integer") . "," .
                        $ilDB->quote($a_obj_id, "integer") . "," .
                        $ilDB->quote($a_obj_type, "text") . "," .
                        // PHP8 Review: Type cast is unnecessary
                        $ilDB->quote((int) $a_sub_obj_id, "integer") . "," .
                        $ilDB->quote($a_sub_obj_type, "text") . "," .
                        $ilDB->quote($tag, "text") . ")");
                    $inserted[] = strtolower($tag);
                }
            }
        }
    }

    // Get tags for a user and an object.
    public static function getTagsForUserAndObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type,
        int $a_user_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $q = "SELECT * FROM il_tag WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            // PHP8 Review: Type cast is unnecessary
            "sub_obj_id = " . $ilDB->quote((int) $a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true) .
            " ORDER BY tag";
        $set = $ilDB->query($q);
        $tags = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $tags[] = $rec["tag"];
        }

        return $tags;
    }

    // Get tags for an object.
    public static function getTagsForObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type,
        bool $a_only_online = true
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $online_str = ($a_only_online)
            ? $online_str = " AND is_offline = " . $ilDB->quote(0, "integer") . " "
            : "";

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $q = "SELECT count(user_id) as cnt, tag FROM il_tag WHERE " .
            "obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote($a_sub_obj_id, "integer") . " AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true) .
            $online_str .
            "GROUP BY tag ORDER BY tag ASC";
        $set = $ilDB->query($q);
        $tags = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $tags[] = $rec;
        }

        return $tags;
    }

    // Get tags for a user.
    public static function getTagsForUser(
        int $a_user_id,
        int $a_max = 0,
        bool $a_only_online = true
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $online_str = ($a_only_online)
            ? $online_str = " AND is_offline = " . $ilDB->quote(0, "integer") . " "
            : "";

        $set = $ilDB->query("SELECT count(*) as cnt, tag FROM il_tag WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " " .
            $online_str .
            " GROUP BY tag ORDER BY cnt DESC");
        $tags = array();
        $cnt = 1;
        while (($rec = $ilDB->fetchAssoc($set)) &&
            ($a_max == 0 || $cnt <= $a_max)) {
            $tags[] = $rec;
            $cnt++;
        }
        $tags = ilArrayUtil::sortArray($tags, "tag", "asc");

        return $tags;
    }

    // Get objects for tag and user
    public static function getObjectsForTagAndUser(
        int $a_user_id,
        string $a_tag
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_tag WHERE " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND tag = " . $ilDB->quote($a_tag, "text");

        $set = $ilDB->query($q);
        $objects = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (ilObject::_exists((int) $rec["obj_id"])) {
                if ($rec["sub_obj_type"] == "-") {
                    $rec["sub_obj_type"] = "";
                }
                $objects[] = $rec;
            } else {
                ilTagging::deleteTagsOfObject(
                    $rec["obj_id"],
                    $rec["obj_type"],
                    $rec["sub_obj_id"],
                    $rec["sub_obj_type"]
                );
            }
        }

        return $objects;
    }

    //Get style class for tag relevance
    public static function getRelevanceClass(
        int $cnt,
        int $max
    ): string {
        $m = $cnt / $max;
        if ($m >= 0.8) {
            return "ilTagRelVeryHigh";
        } elseif ($m >= 0.6) {
            return "ilTagRelHigh";
        } elseif ($m >= 0.4) {
            return "ilTagRelMiddle";
        } elseif ($m >= 0.2) {
            return "ilTagRelLow";
        }

        return "ilTagRelVeryLow";
    }

    // Set offline
    public static function setTagsOfObjectOffline(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type,
        bool $a_offline = true
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->manipulateF(
            "UPDATE il_tag SET is_offline = %s " .
            "WHERE " .
            "obj_id = %s AND " .
            "obj_type = %s AND " .
            "sub_obj_id = %s AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true),
            array("boolean", "integer", "text", "integer"),
            array($a_offline, $a_obj_id, $a_obj_type, $a_sub_obj_id)
        );
    }

    // Deletes tags of an object
    public static function deleteTagsOfObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->manipulateF(
            "DELETE FROM il_tag " .
            "WHERE " .
            "obj_id = %s AND " .
            "obj_type = %s AND " .
            "sub_obj_id = %s AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true),
            array("integer", "text", "integer"),
            array($a_obj_id, $a_obj_type, $a_sub_obj_id)
        );
    }

    // Deletes tag of an object
    public static function deleteTagOfObjectForUser(
        int $a_user_id,
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id,
        string $a_sub_obj_type,
        string $a_tag
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->manipulateF(
            "DELETE FROM il_tag " .
            "WHERE " .
            "user_id = %s AND " .
            "obj_id = %s AND " .
            "obj_type = %s AND " .
            "sub_obj_id = %s AND " .
            $ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true) . " AND " .
            "tag = %s",
            array("integer", "integer", "text", "integer", "text"),
            array($a_user_id, $a_obj_id, $a_obj_type, $a_sub_obj_id, $a_tag)
        );
    }

    // Get users for tag
    public static function getUsersForTag(
        string $a_tag
    ): array {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT DISTINCT user_id, firstname, lastname FROM il_tag JOIN usr_data ON (user_id = usr_id) " .
            " WHERE LOWER(tag) = LOWER(" . $ilDB->quote($a_tag, "text") . ")" .
            " ORDER BY lastname, firstname"
        );
        $users = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $users[] = array("id" => $rec["user_id"]);
        }
        return $users;
    }

    /**
     * Count all tags for repository objects
     * @param int[] $a_obj_ids
     * @param bool  $a_all_users
     * @return int[] key is object id
     */
    public static function _countTags(
        array $a_obj_ids,
        bool $a_all_users = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $q = "SELECT count(*) c, obj_id FROM il_tag WHERE " .
            $ilDB->in("obj_id", $a_obj_ids, false, "integer");
        // PHP8 Review: Type cast is unnecessary
        if (!(bool) $a_all_users) {
            $q .= " AND user_id = " . $ilDB->quote($ilUser->getId(), "integer");
        }
        $q .= " GROUP BY obj_id";

        $cnt = array();
        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cnt[$rec["obj_id"]] = $rec["c"];
        }

        return $cnt;
    }

    /**
     * Count tags for given object ids
     * @param int[]    $a_obj_ids
     */
    public static function _getTagCloudForObjects(
        array $a_obj_ids,
        ?int $a_user_id = null,
        int $a_divide = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $sql = "SELECT obj_id, obj_type, tag, user_id" .
            " FROM il_tag" .
            " WHERE " . $ilDB->in("obj_id", array_keys($a_obj_ids), false, "integer") .
            " AND is_offline = " . $ilDB->quote(0, "integer");
        if ($a_user_id) {
            $sql .= " AND user_id = " . $ilDB->quote($a_user_id, "integer");
        }
        $sql .= " ORDER BY tag";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($a_obj_ids[$row["obj_id"]] == $row["obj_type"]) {
                $tag = $row["tag"];

                if ($a_divide > 0) {
                    if ($row["user_id"] == $a_divide) {
                        $res["personal"][$tag] = isset($res["personal"][$tag])
                            ? $res["personal"][$tag]++
                            : 1;
                    } else {
                        $res["other"][$tag] = isset($res["other"][$tag])
                            ? $res["other"][$tag]++
                            : 1;
                    }
                } else {
                    $res[$tag] = isset($res[$tag])
                        ? $res[$tag]++
                        : 1;
                }
            }
        }

        return $res;
    }

    /**
     * Find all objects with given tag
     * @param ?int $a_user_id
     * @return int[]
     */
    public static function _findObjectsByTag(
        string $a_tag,
        int $a_user_id = null,
        bool $a_invert = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $sql = "SELECT obj_id, obj_type" .
            " FROM il_tag" .
            " WHERE tag = " . $ilDB->quote($a_tag, "text") .
            " AND is_offline = " . $ilDB->quote(0, "integer");
        if ($a_user_id) {
            if (!$a_invert) {
                $sql .= " AND user_id = " . $ilDB->quote($a_user_id, "integer");
            } else {
                $sql .= " AND user_id <> " . $ilDB->quote($a_user_id, "integer");
            }
        }
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["obj_id"]] = $row["obj_type"];
        }

        return $res;
    }

    /**
     * Get tags for given object ids
     * @param array $a_obj_ids
     * @param ?int $a_user_id
     * @return array
     */
    public static function _getListTagsForObjects(
        array $a_obj_ids,
        int $a_user_id = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $res = array();

        $sql = "SELECT obj_id, tag, user_id" .
            " FROM il_tag" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, false, "integer") .
            " AND is_offline = " . $ilDB->quote(0, "integer");
        if ($a_user_id) {
            $sql .= " AND user_id = " . $ilDB->quote($a_user_id, "integer");
        }
        $sql .= " ORDER BY tag";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $tag = $row["tag"];
            $res[$row["obj_id"]][$tag] = false;
            if ($row["user_id"] == $ilUser->getId()) {
                $res[$row["obj_id"]][$tag] = true;
            }
        }

        return $res;
    }
}
