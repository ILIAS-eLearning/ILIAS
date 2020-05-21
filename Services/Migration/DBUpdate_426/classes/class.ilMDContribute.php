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
* Meta Data class (element contribute)
*
* @author Stefan Meyer <meyer@leifos.com>
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDContribute extends ilMDBase
{
    // Subelements
    public function &getEntityIds()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDEntity.php';

        return ilMDEntity::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_contribute');
    }
    public function &getEntity($a_entity_id)
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDEntity.php';
        
        if (!$a_entity_id) {
            return false;
        }
        $ent = new ilMDEntity();
        $ent->setMetaId($a_entity_id);

        return $ent;
    }
    public function &addEntity()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDEntity.php';

        $ent = new ilMDEntity($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $ent->setParentId($this->getMetaId());
        $ent->setParentType('meta_contribute');

        return $ent;
    }

    // SET/GET
    public function setRole($a_role)
    {
        switch ($a_role) {
            case 'Author':
            case 'Publisher':
            case 'Unknown':
            case 'Initiator':
            case 'Terminator':
            case 'Editor':
            case 'GraphicalDesigner':
            case 'TechnicalImplementer':
            case 'ContentProvider':
            case 'TechnicalValidator':
            case 'EducationalValidator':
            case 'ScriptWriter':
            case 'InstructionalDesigner':
            case 'SubjectMatterExpert':
            case 'Creator':
            case 'Validator':
                $this->role = $a_role;
                return true;

            default:
                return false;
        }
    }
    public function getRole()
    {
        return $this->role;
    }
    public function setDate($a_date)
    {
        $this->date = $a_date;
    }
    public function getDate()
    {
        return $this->date;
    }


    public function save()
    {
        if ($this->db->autoExecute(
            'il_meta_contribute',
            $this->__getFields(),
            ilDBConstants::AUTOQUERY_INSERT
        )) {
            $this->setMetaId($this->db->getLastInsertId());

            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $ilDB;
        
        if ($this->getMetaId()) {
            if ($this->db->autoExecute(
                'il_meta_contribute',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_contribute_id = " . $ilDB->quote($this->getMetaId())
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        global $ilDB;
        
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_contribute " .
                "WHERE meta_contribute_id = " . $ilDB->quote($this->getMetaId());
            
            $this->db->query($query);
            
            foreach ($this->getEntityIds() as $id) {
                $ent = &$this->getEntity($id);
                $ent->delete();
            }
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id' => $this->getRBACId(),
                     'obj_id' => $this->getObjId(),
                     'obj_type' => ilUtil::prepareDBString($this->getObjType()),
                     'parent_type' => $this->getParentType(),
                     'parent_id' => $this->getParentId(),
                     'role' => ilUtil::prepareDBString($this->getRole()),
                     'date' => ilUtil::prepareDBString($this->getDate()));
    }

    public function read()
    {
        global $ilDB;
        
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_contribute " .
                "WHERE meta_contribute_id = " . $ilDB->quote($this->getMetaId());

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setRole(ilUtil::stripSlashes($row->role));
                $this->setDate(ilUtil::stripSlashes($row->date));
            }
        }
        return true;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag('Contribute', array('Role' => $this->getRole()));

        // Entities
        foreach ($this->getEntityIds() as $id) {
            $ent = &$this->getEntity($id);
            $ent->toXML($writer);
        }
        $writer->xmlElement('Date', null, $this->getDate());
        $writer->xmlEndTag('Contribute');
    }


    // STATIC
    public function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $ilDB;

        $query = "SELECT meta_contribute_id FROM il_meta_contribute " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id) . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id) . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_contribute_id;
        }
        return $ids ? $ids : array();
    }
}
