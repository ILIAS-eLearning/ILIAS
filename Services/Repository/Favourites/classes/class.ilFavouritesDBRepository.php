<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilFavouritesDBRepository
{

    /**
     * @var array
     */
    public static $is_desktop_item = [];

    /**
     * Constructor
     */
    public function __construct(\ilDBInterface $db = null, ilTree $tree = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
        $this->tree = (is_null($tree))
            ? $DIC->repositoryTree()
            : $tree;
    }


    /**
     * Add favourite
     * @param int $user_id
     * @param int $ref_id
     */
    public function add(int $user_id, int $ref_id)
    {
        $db = $this->db;

        $type = ilObject::_lookupType($ref_id, true);

        $item_set = $db->queryF(
            "SELECT * FROM desktop_item WHERE " .
            "item_id = %s AND type = %s AND user_id = %s",
            ["integer", "text", "integer"],
            [$ref_id, $type, $user_id]
        );

        // only insert if item is not already on desktop
        if (!$db->fetchAssoc($item_set)) {
            $db->manipulateF(
                "INSERT INTO desktop_item (item_id, type, user_id, parameters) VALUES " .
                " (%s,%s,%s,%s)",
                array("integer", "text", "integer", "text"),
                array($ref_id,$type,$user_id,"")
            );
        }
    }

    /**
     * Remove favourite
     *
     * @param int $user_id
     * @param int $ref_id
     */
    public function remove(int $user_id, int $ref_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM desktop_item WHERE " .
            " item_id = %s AND user_id = %s",
            array("integer", "integer"),
            array($ref_id, $user_id)
        );
    }


    /**
     * Get all desktop items of user and specified type
     *
     * note: the implementation of this method is not good style (directly
     * reading tables object_data and object_reference), must be revised someday...
     * @param int $user_id
     * @param array|null $a_types
     * @return array
     */
    public function getFavouritesOfUser(int $user_id, array $a_types = null) : array
    {
        $tree = $this->tree;
        $ilDB = $this->db;

        if (is_null($a_types)) {
            $item_set = $ilDB->queryF("SELECT obj.obj_id, obj.description, oref.ref_id, obj.title, obj.type " .
                " FROM desktop_item it, object_reference oref " .
                ", object_data obj" .
                " WHERE " .
                "it.item_id = oref.ref_id AND " .
                "oref.obj_id = obj.obj_id AND " .
                "it.user_id = %s", array("integer"), array($user_id));
            $items = $all_parent_path = array();
            while ($item_rec = $ilDB->fetchAssoc($item_set)) {
                if ($tree->isInTree($item_rec["ref_id"])
                    && $item_rec["type"] != "rolf"
                    && $item_rec["type"] != "itgr") {	// due to bug 11508
                    $parent_ref = $tree->getParentId($item_rec["ref_id"]);

                    if (!isset($all_parent_path[$parent_ref])) {
                        if ($parent_ref > 0) {	// workaround for #0023176
                            $node = $tree->getNodeData($parent_ref);
                            $all_parent_path[$parent_ref] = $node["title"];
                        } else {
                            $all_parent_path[$parent_ref] = "";
                        }
                    }

                    $parent_path = $all_parent_path[$parent_ref];

                    $title = ilObject::_lookupTitle($item_rec["obj_id"]);
                    $desc = ilObject::_lookupDescription($item_rec["obj_id"]);
                    $items[$parent_path . $title . $item_rec["ref_id"]] =
                        array("ref_id" => $item_rec["ref_id"],
                            "obj_id" => $item_rec["obj_id"],
                            "type" => $item_rec["type"],
                            "title" => $title,
                            "description" => $desc,
                            "parent_ref" => $parent_ref);
                }
            }
            ksort($items);
        } else {
            $items = array();
            foreach ($a_types as $a_type) {
                if ($a_type == "itgr") {
                    continue;
                }
                $item_set = $ilDB->queryF(
                    "SELECT obj.obj_id, obj.description, oref.ref_id, obj.title FROM desktop_item it, object_reference oref " .
                    ", object_data obj WHERE " .
                    "it.item_id = oref.ref_id AND " .
                    "oref.obj_id = obj.obj_id AND " .
                    "it.type = %s AND " .
                    "it.user_id = %s " .
                    "ORDER BY title",
                    array("text", "integer"),
                    array($a_type, $user_id)
                );

                while ($item_rec = $ilDB->fetchAssoc($item_set)) {
                    $title = ilObject::_lookupTitle($item_rec["obj_id"]);
                    $desc = ilObject::_lookupDescription($item_rec["obj_id"]);
                    $items[$title . $a_type . $item_rec["ref_id"]] =
                        array("ref_id" => $item_rec["ref_id"],
                            "obj_id" => $item_rec["obj_id"], "type" => $a_type,
                            "title" => $title, "description" => $desc);
                }
            }
            ksort($items);
        }
        return $items;
    }

    /**
     * check wether an item is on the users desktop or not
     * @param $user_id
     * @param $ref_id
     * @return bool
     */
    public function ifIsFavourite($user_id, $ref_id)
    {
        $db = $this->db;

        if (!isset(self::$is_desktop_item[$user_id . ":" . $ref_id])) {
            $item_set = $db->queryF(
                "SELECT item_id FROM desktop_item WHERE " .
                "item_id = %s AND user_id = %s",
                array("integer", "integer"),
                array($ref_id, $user_id)
            );

            if ($db->fetchAssoc($item_set)) {
                self::$is_desktop_item[$user_id . ":" . $ref_id] = true;
            } else {
                self::$is_desktop_item[$user_id . ":" . $ref_id] = false;
            }
        }
        return self::$is_desktop_item[$user_id . ":" . $ref_id];
    }

    /**
     * Load favourites data
     * @param int $user_id
     * @param array $ref_ids
     */
    public function loadData(int $user_id, array $ref_ids)
    {
        $db = $this->db;
        if (!is_array($ref_ids)) {
            return;
        }

        $load_ref_ids = [];
        foreach ($ref_ids as $ref_id) {
            if (!isset(self::$is_desktop_item[$user_id . ":" . $ref_id])) {
                $load_ref_ids[] = $ref_id;
            }
        }

        if (count($load_ref_ids) > 0) {
            $item_set = $db->query("SELECT item_id FROM desktop_item WHERE " .
                $db->in("item_id", $load_ref_ids, false, "integer") .
                " AND user_id = " . $db->quote($user_id, "integer"));
            while ($r = $db->fetchAssoc($item_set)) {
                self::$is_desktop_item[$user_id . ":" . $r["item_id"]] = true;
            }
            foreach ($load_ref_ids as $ref_id) {
                if (!isset(self::$is_desktop_item[$user_id . ":" . $ref_id])) {
                    self::$is_desktop_item[$user_id . ":" . $ref_id] = false;
                }
            }
        }
    }

    /**
     * Remove favourite entries of a repository item
     *
     * @param int $ref_id
     */
    public function removeFavouritesOfRefId(int $ref_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM desktop_item WHERE " .
            " item_id = %s",
            ["integer"],
            [$ref_id]
        );
    }

    /**
     * Remove favourite entries of a user
     *
     * @param int $user_id
     */
    public function removeFavouritesOfUser(int $user_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM desktop_item WHERE " .
            " user_id = %s",
            ["integer"],
            [$user_id]
        );
    }
}
