<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* class for editing lm menu
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMMenuEditor
{
    protected $active = "n";

    /**
     * @var ilDB
     */
    protected $db;

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;
        $this->link_type = "extern";
        $this->link_ref_id = null;
    }

    public function setObjId($a_obj_id)
    {
        $this->lm_id = $a_obj_id;
    }

    public function getObjId()
    {
        return $this->lm_id;
    }

    public function setEntryId($a_id)
    {
        $this->entry_id = $a_id;
    }

    public function getEntryId()
    {
        return $this->entry_id;
    }

    public function setLinkType($a_link_type)
    {
        $this->link_type = $a_link_type;
    }
    
    public function getLinkType()
    {
        return $this->link_type;
    }
    
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTarget($a_target)
    {
        $this->target = $a_target;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setLinkRefId($a_link_ref_id)
    {
        $this->link_ref_id = $a_link_ref_id;
    }

    public function getLinkRefId()
    {
        return $this->link_ref_id;
    }
    
    /**
     * Set active
     *
     * @param string $a_val
     */
    public function setActive($a_val)
    {
        $this->active = $a_val;
    }
    
    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }
    

    public function create()
    {
        $ilDB = $this->db;
        
        $id = $ilDB->nextId("lm_menu");
        $q = "INSERT INTO lm_menu (id, lm_id,link_type,title,target,link_ref_id, active) " .
             "VALUES " .
             "(" .
             $ilDB->quote($id, "integer") . "," .
             $ilDB->quote((int) $this->getObjId(), "integer") . "," .
             $ilDB->quote($this->getLinkType(), "text") . "," .
             $ilDB->quote($this->getTitle(), "text") . "," .
             $ilDB->quote($this->getTarget(), "text") . "," .
             $ilDB->quote((int) $this->getLinkRefId(), "integer") . "," .
             $ilDB->quote($this->getActive(), "text") .
            ")";
        $r = $ilDB->manipulate($q);

        $this->entry_id = $id;

        return true;
    }
    
    public function getMenuEntries($a_only_active = false)
    {
        $ilDB = $this->db;
        
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
    
    /**
     * delete menu entry
     *
     */
    public function delete($a_id)
    {
        $ilDB = $this->db;
        
        if (!$a_id) {
            return false;
        }
        
        $q = "DELETE FROM lm_menu WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
        
        return true;
    }
    
    /**
     * update menu entry
     *
     */
    public function update()
    {
        $ilDB = $this->db;
        
        $q = "UPDATE lm_menu SET " .
            " link_type = " . $ilDB->quote($this->getLinkType(), "text") . "," .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " target = " . $ilDB->quote($this->getTarget(), "text") . "," .
            " link_ref_id = " . $ilDB->quote((int) $this->getLinkRefId(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getEntryId(), "integer");
        $r = $ilDB->manipulate($q);
        
        return true;
    }
    
    public function readEntry($a_id)
    {
        $ilDB = $this->db;
        
        if (!$a_id) {
            return false;
        }
        
        $q = "SELECT * FROM lm_menu WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $r = $ilDB->query($q);

        $row = $ilDB->fetchObject($r);
        
        $this->setTitle($row->title);
        $this->setTarget($row->target);
        $this->setLinkType($row->link_type);
        $this->setLinkRefId($row->link_ref_id);
        $this->setEntryid($a_id);
        $this->setActive($row->active);
    }
    
    /**
     * update active status of all menu entries of lm
     * @param	array	entry ids
     *
     */
    public function updateActiveStatus($a_entries)
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

    /**
     * Fix ref ids on import
     *
     * @param int $new_lm_id
     * @param array $ref_mapping
     */
    public static function fixImportMenuItems(int $new_lm_id, array $ref_mapping)
    {
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

    /**
     * Write status for entry id
     *
     * @param $entry_id
     * @param $active
     */
    public static function writeActive($entry_id, $active)
    {
        global $DIC;

        $db = $DIC->database();

        $db->update("lm_menu", array(
                "active" => array("text", ($active ? "y" : "n"))
            ), array(	// where
                "id" => array("", $entry_id)
            ));
    }
}
