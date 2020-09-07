<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Meta Data class (element identifier)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDIdentifier extends ilMDBase
{

    // SET/GET
    public function setCatalog($a_catalog)
    {
        $this->catalog = $a_catalog;
    }
    public function getCatalog()
    {
        return $this->catalog;
    }
    public function setEntry($a_entry)
    {
        $this->entry = $a_entry;
    }
    public function getEntry()
    {
        return $this->entry;
    }


    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_identifier_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_identifier'));
        
        if ($this->db->insert('il_meta_identifier', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_identifier',
                $this->__getFields(),
                array("meta_identifier_id" => array('integer',$this->getMetaId()))
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_identifier " .
                "WHERE meta_identifier_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id' => array('integer',$this->getRBACId()),
                     'obj_id' => array('integer',$this->getObjId()),
                     'obj_type' => array('text',$this->getObjType()),
                     'parent_type' => array('text',$this->getParentType()),
                     'parent_id' => array('integer',$this->getParentId()),
                     'catalog' => array('text',$this->getCatalog()),
                     'entry' => array('text',$this->getEntry()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_identifier " .
                "WHERE meta_identifier_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setCatalog($row->catalog);
                $this->setEntry($row->entry);
            }
        }
        return true;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer, $a_overwrite_id = false)
    {
        $entry_default = ($this->getObjId() == 0)
            ? "il_" . IL_INST_ID . "_" . $this->getObjType() . "_" . $this->getRBACId()
            : "il_" . IL_INST_ID . "_" . $this->getObjType() . "_" . $this->getObjId();

        $entry = $this->getEntry() ? $this->getEntry() : $entry_default;
        $catalog = $this->getCatalog();

        if ($this->getExportMode() && $this->getCatalog() != "ILIAS_NID") {
            $entry = $entry_default;
            $catalog = "ILIAS";
        }

        if (strlen($catalog)) {
            $writer->xmlElement('Identifier', array('Catalog' => $catalog,
                                                   'Entry' => $entry));
        } else {
            $writer->xmlElement('Identifier', array('Entry' => $entry));
        }
    }


    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_identifier_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_identifier_id;
        }
        return $ids ? $ids : array();
    }

    /**
     * Get IDs for an object
     *
     * @param
     * @return
     */
    public static function _getEntriesForObj($a_rbac_id, $a_obj_id, $a_obj_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_identifier_id, catalog, entry FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_obj_type, 'text');

        $res = $ilDB->query($query);
        $entries = array();
        while ($r = $ilDB->fetchAssoc($res)) {
            $entries[$r["meta_identifier_id"]] =
                array("catalog" => $r["catalog"],
                    "entry" => $r["entry"]);
        }
        return $entries;
    }

    /**
     * Get IDs for an rbac object
     *
     * @param
     * @return
     */
    public static function _getEntriesForRbacObj($a_rbac_id, $a_obj_type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_identifier_id, catalog, entry, obj_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer');

        if ($a_obj_type != "") {
            $query .=
                " AND obj_type = " . $ilDB->quote($a_obj_type, 'text');
        }

        $res = $ilDB->query($query);
        $entries = array();
        while ($r = $ilDB->fetchAssoc($res)) {
            $entries[$r["meta_identifier_id"]] =
                array("catalog" => $r["catalog"],
                    "entry" => $r["entry"],
                    "obj_id" => $r["obj_id"]);
        }
        return $entries;
    }

    /**
     * Does id entry exist in rbac object?
     *
     * @param
     * @return
     */
    public static function existsIdInRbacObject($a_rbac_id, $a_obj_type, $a_catalog, $a_entry)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_identifier_id, obj_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') .
            " AND obj_type = " . $ilDB->quote($a_obj_type, 'text') .
            " AND catalog = " . $ilDB->quote($a_catalog, 'text') .
            " AND entry = " . $ilDB->quote($a_entry, 'text');
        $s = $ilDB->query($query);
        if ($r = $ilDB->fetchAssoc($s)) {
            return true;
        }
        return false;
    }
    
    /**
     * Does id entry exist in rbac object?
     *
     * @param
     * @return
     */
    public static function readIdData($a_rbac_id, $a_obj_type, $a_catalog, $a_entry)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') .
            " AND obj_type = " . $ilDB->quote($a_obj_type, 'text') .
            " AND catalog = " . $ilDB->quote($a_catalog, 'text') .
            " AND entry = " . $ilDB->quote($a_entry, 'text');
        $s = $ilDB->query($query);
        $data = array();
        while ($r = $ilDB->fetchAssoc($s)) {
            $data[] = $r;
        }
        return $data;
    }
}
