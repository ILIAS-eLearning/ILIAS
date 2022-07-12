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
 * class for editing lm menu
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilLMMenuEditor
{
    protected string $target = "";
    protected string $title = "";
    protected int $entry_id = 0;
    protected int $lm_id = 0;
    protected ?int $link_ref_id;
    protected string $link_type;
    protected string $active = "n";
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;
        $this->link_type = "extern";
        $this->link_ref_id = null;
    }

    public function setObjId(int $a_obj_id) : void
    {
        $this->lm_id = $a_obj_id;
    }

    public function getObjId() : int
    {
        return $this->lm_id;
    }

    public function setEntryId(int $a_id) : void
    {
        $this->entry_id = $a_id;
    }

    public function getEntryId() : int
    {
        return $this->entry_id;
    }

    public function setLinkType(string $a_link_type) : void
    {
        $this->link_type = $a_link_type;
    }
    
    public function getLinkType() : string
    {
        return $this->link_type;
    }
    
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setTarget(string $a_target) : void
    {
        $this->target = $a_target;
    }
    
    public function getTarget() : string
    {
        return $this->target;
    }
    
    public function setLinkRefId(int $a_link_ref_id) : void
    {
        $this->link_ref_id = $a_link_ref_id;
    }

    public function getLinkRefId() : int
    {
        return $this->link_ref_id;
    }
    
    public function setActive(string $a_val) : void
    {
        $this->active = $a_val;
    }
    
    public function getActive() : string
    {
        return $this->active;
    }
    

    public function create() : void
    {
        $ilDB = $this->db;
        
        $id = $ilDB->nextId("lm_menu");
        $q = "INSERT INTO lm_menu (id, lm_id,link_type,title,target,link_ref_id, active) " .
             "VALUES " .
             "(" .
             $ilDB->quote($id, "integer") . "," .
             $ilDB->quote($this->getObjId(), "integer") . "," .
             $ilDB->quote($this->getLinkType(), "text") . "," .
             $ilDB->quote($this->getTitle(), "text") . "," .
             $ilDB->quote($this->getTarget(), "text") . "," .
             $ilDB->quote($this->getLinkRefId(), "integer") . "," .
             $ilDB->quote($this->getActive(), "text") .
            ")";
        $ilDB->manipulate($q);
        $this->entry_id = $id;
    }
    
    public function getMenuEntries(
        bool $a_only_active = false
    ) : array {
        $ilDB = $this->db;

        $and = "";
        
        $entries = array();
        
        if ($a_only_active === true) {
            $and = " AND active = " . $ilDB->quote("y", "text");
        }
        
        $q = "SELECT * FROM lm_menu " .
             "WHERE lm_id = " . $ilDB->quote($this->lm_id, "integer") .
             $and;
             
        $r = $ilDB->query($q);

        while ($row = $ilDB->fetchObject($r)) {
            $entries[] = array('id' => $row->id,
                               'title' => $row->title,
                               'link' => $row->target,
                               'type' => $row->link_type,
                               'ref_id' => $row->link_ref_id,
                               'active' => $row->active
                               );
        }

        return $entries;
    }
    
    public function delete(int $a_id) : void
    {
        $ilDB = $this->db;
        
        $q = "DELETE FROM lm_menu WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
    }
    
    public function update() : void
    {
        $ilDB = $this->db;
        
        $q = "UPDATE lm_menu SET " .
            " link_type = " . $ilDB->quote($this->getLinkType(), "text") . "," .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " target = " . $ilDB->quote($this->getTarget(), "text") . "," .
            " link_ref_id = " . $ilDB->quote($this->getLinkRefId(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getEntryId(), "integer");
        $ilDB->manipulate($q);
    }
    
    public function readEntry(int $a_id) : void
    {
        $ilDB = $this->db;
        
        if (!$a_id) {
            return;
        }
        
        $q = "SELECT * FROM lm_menu WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $r = $ilDB->query($q);

        $row = $ilDB->fetchObject($r);
        
        $this->setTitle($row->title);
        $this->setTarget($row->target);
        $this->setLinkType($row->link_type);
        $this->setLinkRefId($row->link_ref_id);
        $this->setEntryId($a_id);
        $this->setActive($row->active);
    }
    
    /**
     * update active status of all menu entries of lm
     */
    public function updateActiveStatus(array $a_entries) : void
    {
        $ilDB = $this->db;
        
        // update active status
        $q = "UPDATE lm_menu SET " .
             "active = CASE " .
             "WHEN " . $ilDB->in("id", $a_entries, false, "integer") . " " .
             "THEN " . $ilDB->quote("y", "text") . " " .
             "ELSE " . $ilDB->quote("n", "text") . " " .
             "END " .
             "WHERE lm_id = " . $ilDB->quote($this->lm_id, "integer");

        $ilDB->manipulate($q);
    }

    public static function fixImportMenuItems(
        int $new_lm_id,
        array $ref_mapping
    ) : void {
        global $DIC;

        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT * FROM lm_menu " .
            " WHERE lm_id = %s ",
            array("integer"),
            array($new_lm_id)
        );
        while ($rec = $db->fetchAssoc($set)) {
            // ... only check internal links
            if ($rec["link_type"] == "intern") {
                $link = explode("_", $rec["link_ref_id"]);
                $ref_id = (int) $link[count($link) - 1];
                $new_ref_id = $ref_mapping[$ref_id];
                // if ref id has been imported, update it
                if ($new_ref_id > 0) {
                    $new_target = str_replace((string) $ref_id, (string) $new_ref_id, $rec["target"]);
                    $db->update("lm_menu", array(
                            "link_ref_id" => array("integer", $new_ref_id),
                            "target" => array("text", $new_target)
                        ), array(	// where
                            "id" => array("integer", $rec["id"])
                        ));
                } else {	// if not, delete the menu item
                    $db->manipulateF(
                        "DELETE FROM lm_menu WHERE " .
                        " id = %s",
                        array("integer"),
                        array($rec["id"])
                    );
                }
            }
        }
    }

    public static function writeActive(
        int $entry_id,
        bool $active
    ) : void {
        global $DIC;

        $db = $DIC->database();

        $db->update("lm_menu", array(
                "active" => array("text", ($active ? "y" : "n"))
            ), array(	// where
                "id" => array("", $entry_id)
            ));
    }
}
