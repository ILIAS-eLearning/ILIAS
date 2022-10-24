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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFavouritesDBRepository
{
    /** @var array<string, bool>  */
    public static array $is_desktop_item = [];
    protected ilDBInterface $db;
    protected ilTree $tree;

    public function __construct(
        ilDBInterface $db = null,
        ilTree $tree = null
    ) {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
        $this->tree = (is_null($tree))
            ? $DIC->repositoryTree()
            : $tree;
    }


    // Add favourite
    public function add(int $user_id, int $ref_id): void
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
                ["integer", "text", "integer", "text"],
                [$ref_id, $type, $user_id, ""]
            );
        }
    }

    // Remove favourite
    public function remove(int $user_id, int $ref_id): void
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM desktop_item WHERE " .
            " item_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$ref_id, $user_id]
        );
    }


    /**
     * Get all desktop items of user and specified type
     *
     * note: the implementation of this method is not good style (directly
     * reading tables object_data and object_reference), must be revised someday...
     */
    public function getFavouritesOfUser(int $user_id, ?array $a_types = null): array
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
                "it.user_id = %s", ["integer"], [$user_id]);
            $items = $all_parent_path = [];
            while ($item_rec = $ilDB->fetchAssoc($item_set)) {
                if ($item_rec["type"] !== "rolf" &&
                    $item_rec["type"] !== "itgr" &&
                    $tree->isInTree((int) $item_rec["ref_id"])) { // due to bug 11508
                    $parent_ref = $tree->getParentId((int) $item_rec["ref_id"]);

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
                        [
                            "ref_id" => (int) $item_rec["ref_id"],
                            "obj_id" => (int) $item_rec["obj_id"],
                            "type" => $item_rec["type"],
                            "title" => $title,
                            "description" => $desc,
                            "parent_ref" => (int) $parent_ref
                        ];
                }
            }
        } else {
            $items = [];
            foreach ($a_types as $a_type) {
                if ($a_type === "itgr") {
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
                    ["text", "integer"],
                    [$a_type, $user_id]
                );

                while ($item_rec = $ilDB->fetchAssoc($item_set)) {
                    $title = ilObject::_lookupTitle($item_rec["obj_id"]);
                    $desc = ilObject::_lookupDescription($item_rec["obj_id"]);
                    $items[$title . $a_type . $item_rec["ref_id"]] =
                        [
                            "ref_id" => (int) $item_rec["ref_id"],
                            "obj_id" => (int) $item_rec["obj_id"],
                            "type" => $a_type,
                            "title" => $title,
                            "description" => $desc
                        ];
                }
            }
        }
        ksort($items);
        return $items;
    }

    // check whether an item is on the users desktop or not
    public function ifIsFavourite(int $user_id, int $ref_id): bool
    {
        $db = $this->db;

        if (!isset(self::$is_desktop_item[$user_id . ":" . $ref_id])) {
            $item_set = $db->queryF(
                "SELECT item_id FROM desktop_item WHERE " .
                "item_id = %s AND user_id = %s",
                ["integer", "integer"],
                [$ref_id, $user_id]
            );

            if ($db->fetchAssoc($item_set)) {
                self::$is_desktop_item[$user_id . ":" . $ref_id] = true;
            } else {
                self::$is_desktop_item[$user_id . ":" . $ref_id] = false;
            }
        }
        return self::$is_desktop_item[$user_id . ":" . $ref_id];
    }

    // Load favourites data
    public function loadData(int $user_id, array $ref_ids): void
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

    // Remove favourite entries of a repository item
    public function removeFavouritesOfRefId(int $ref_id): void
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM desktop_item WHERE " .
            " item_id = %s",
            ["integer"],
            [$ref_id]
        );
    }

    // Remove favourite entries of a user
    public function removeFavouritesOfUser(int $user_id): void
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
