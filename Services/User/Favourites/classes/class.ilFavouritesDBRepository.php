<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 * @ingroup
 */
class ilFavouritesDBRepository
{
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
     * get all desktop items of user and specified type
     *
     * note: the implementation of this method is not good style (directly
     * reading tables object_data and object_reference), must be revised someday...
     */
    function getFavouritesOfUser($user_id, $a_types = "")
    {
        $tree = $this->tree;
        $ilDB = $this->db;

        if ($a_types == "")
        {
            $item_set = $ilDB->queryF("SELECT obj.obj_id, obj.description, oref.ref_id, obj.title, obj.type ".
                " FROM desktop_item it, object_reference oref ".
                ", object_data obj".
                " WHERE ".
                "it.item_id = oref.ref_id AND ".
                "oref.obj_id = obj.obj_id AND ".
                "it.user_id = %s", array("integer"), array($user_id));
            $items = $all_parent_path = array();
            while ($item_rec = $ilDB->fetchAssoc($item_set))
            {
                if ($tree->isInTree($item_rec["ref_id"])
                    && $item_rec["type"] != "rolf"
                    && $item_rec["type"] != "itgr")	// due to bug 11508
                {
                    $parent_ref = $tree->getParentId($item_rec["ref_id"]);

                    if (!isset($all_parent_path[$parent_ref]))
                    {
                        if ($parent_ref > 0)	// workaround for #0023176
                        {
                            $node = $tree->getNodeData($parent_ref);
                            $all_parent_path[$parent_ref] = $node["title"];
                        }
                        else
                        {
                            $all_parent_path[$parent_ref] = "";
                        }
                    }

                    $parent_path = $all_parent_path[$parent_ref];

                    $title = ilObject::_lookupTitle($item_rec["obj_id"]);
                    $desc = ilObject::_lookupDescription($item_rec["obj_id"]);
                    $items[$parent_path.$title.$item_rec["ref_id"]] =
                        array("ref_id" => $item_rec["ref_id"],
                            "obj_id" => $item_rec["obj_id"],
                            "type" => $item_rec["type"],
                            "title" => $title,
                            "description" => $desc,
                            "parent_ref" => $parent_ref);
                }
            }
            ksort($items);
        }
        else
        {
            // due to bug 11508
            if (!is_array($a_types))
            {
                $a_types = array($a_types);
            }
            $items = array();
            foreach($a_types as $a_type)
            {
                if ($a_type == "itgr")
                {
                    continue;
                }
                $item_set = $ilDB->queryF("SELECT obj.obj_id, obj.description, oref.ref_id, obj.title FROM desktop_item it, object_reference oref ".
                    ", object_data obj WHERE ".
                    "it.item_id = oref.ref_id AND ".
                    "oref.obj_id = obj.obj_id AND ".
                    "it.type = %s AND ".
                    "it.user_id = %s ".
                    "ORDER BY title",
                    array("text", "integer"),
                    array($a_type, $user_id));

                while ($item_rec = $ilDB->fetchAssoc($item_set))
                {
                    $title = ilObject::_lookupTitle($item_rec["obj_id"]);
                    $desc = ilObject::_lookupDescription($item_rec["obj_id"]);
                    $items[$title.$a_type.$item_rec["ref_id"]] =
                        array("ref_id" => $item_rec["ref_id"],
                            "obj_id" => $item_rec["obj_id"], "type" => $a_type,
                            "title" => $title, "description" => $desc);
                }

            }
            ksort($items);
        }

        return $items;
    }

}