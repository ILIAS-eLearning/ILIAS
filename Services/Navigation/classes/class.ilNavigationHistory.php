<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Navigation History of Repository Items
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNavigationHistory
{
    protected \ILIAS\Navigation\NavigationSessionRepository $repo;
    protected ilObjUser $user;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected ilComponentRepository $component_repository;
    private array $items;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->items = array();

        $this->repo = new \ILIAS\Navigation\NavigationSessionRepository();
        $this->items = $this->repo->getHistory();
        $this->component_repository = $DIC["component.repository"];
    }

    /**
     * Add an item to the stack. If ref_id is already used,
     * the item is moved to the top.
     */
    public function addItem(
        int $a_ref_id,
        string $a_link,
        string $a_type,
        string $a_title = "",
        ?int $a_sub_obj_id = null,
        string $a_goto_link = ""
    ) : void {
        $ilUser = $this->user;
        $ilDB = $this->db;

        // never store?
        if ((int) ($ilUser->prefs["store_last_visited"] ?? 0) == 2) {
            return;
        }
        
        $a_sub_obj_id = (string) $a_sub_obj_id;
        
        if ($a_title === "" && $a_ref_id > 0) {
            $obj_id = ilObject::_lookupObjId($a_ref_id);
            if (ilObject::_exists($obj_id)) {
                $a_title = ilObject::_lookupTitle($obj_id);
            }
        }

        $id = $a_ref_id . ":" . $a_sub_obj_id;

        $new_items[$id] = array("id" => $id,"ref_id" => $a_ref_id, "link" => $a_link, "title" => $a_title,
            "type" => $a_type, "sub_obj_id" => $a_sub_obj_id, "goto_link" => $a_goto_link);
        
        $cnt = 1;
        foreach ($this->items as $key => $item) {
            if ($item["id"] != $id && $cnt <= 10) {
                $new_items[$item["id"]] = $item;
                $cnt++;
            }
        }
        
        // put items in session
        $this->items = $new_items;

        $this->repo->setHistory($this->items);

        // only store in session?
        if ((int) ($ilUser->prefs["store_last_visited"] ?? 0) == 1) {
            return;
        }


        // update entries in db
        $ilDB->update(
            "usr_data",
            array(
                    "last_visited" => array("clob", serialize($this->getItems()))),
            array(
                "usr_id" => array("integer", $ilUser->getId()))
        );
    }
    
    /**
     * Get navigation item stack.
     */
    public function getItems() : array
    {
        $tree = $this->tree;
        $ilDB = $this->db;
        $ilUser = $this->user;
        $objDefinition = $this->obj_definition;
        $component_repository = $this->component_repository;
        
        $items = array();
        
        foreach ($this->items as $it) {
            if (
                $tree->isInTree($it["ref_id"]) &&
                (
                    !$objDefinition->isPluginTypeName($it["type"]) ||
                    $component_repository->getPluginById($it["type"])->isActive()
                )
            ) {
                $items[$it["ref_id"] . ":" . $it["sub_obj_id"]] = $it;
            }
        }
        // less than 10? -> get items from db
        if (count($items) < 10 && $ilUser->getId() !== ANONYMOUS_USER_ID) {
            $set = $ilDB->query(
                "SELECT last_visited FROM usr_data " .
                " WHERE usr_id = " . $ilDB->quote($ilUser->getId(), "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            $db_entries = unserialize($rec["last_visited"], ["allowed_classes" => false]);
            $cnt = count($items);
            if (is_array($db_entries)) {
                foreach ($db_entries as $rec) {
                    if ($cnt <= 10 && !isset($items[$rec["ref_id"] . ":" . $rec["sub_obj_id"]]) && $tree->isInTree((int) $rec["ref_id"]) &&
                        (
                            !$objDefinition->isPluginTypeName($rec["type"]) ||
                            $component_repository->getPluginById($rec["type"])->isActive()
                        )) {
                        $link = ($rec["goto_link"] != "")
                                ? $rec["goto_link"]
                                : ilLink::_getLink((int) $rec["ref_id"]);
                        if ($rec["sub_obj_id"] != "") {
                            $title = $rec["title"];
                        } else {
                            $title = ilObject::_lookupTitle(ilObject::_lookupObjId((int) $rec["ref_id"]));
                        }
                        $items[$rec["ref_id"] . ":" . $rec["sub_obj_id"]] = array("id" => $rec["ref_id"] . ":" . $rec["sub_obj_id"],
                                "ref_id" => $rec["ref_id"], "link" => $link, "title" => $title,
                                "type" => $rec["type"], "sub_obj_id" => $rec["sub_obj_id"], "goto_link" => $rec["goto_link"]);
                        $cnt++;
                    }
                }
            }
        }
        return $items;
    }
    
    public function deleteDBEntries() : void
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        
        // update entries in db
        $ilDB->update(
            "usr_data",
            array(
                    "last_visited" => array("clob", serialize(array()))),
            array(
                "usr_id" => array("integer", $ilUser->getId()))
        );
    }

    public function deleteSessionEntries() : void
    {
        $this->repo->setHistory([]);
    }
}
