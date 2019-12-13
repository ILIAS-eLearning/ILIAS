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
* Meta Data class (element requirement)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDRequirement extends ilMDBase
{
    // SET/GET
    public function setOrCompositeId($a_or_composite_id)
    {
        $this->or_composite_id = (int) $a_or_composite_id;
    }
    public function getOrCompositeId()
    {
        return (int) $this->or_composite_id;
    }


    public function setOperatingSystemName($a_val)
    {
        switch ($a_val) {
            case 'PC-DOS':
            case 'MS-Windows':
            case 'MacOS':
            case 'Unix':
            case 'Multi-OS':
            case 'None':
                $this->operating_system_name = $a_val;
                return true;

            default:
                return false;
        }
    }
    public function getOperatingSystemName()
    {
        return $this->operating_system_name;
    }
    public function setOperatingSystemMinimumVersion($a_val)
    {
        $this->operating_system_minimum_version = $a_val;
    }
    public function getOperatingSystemMinimumVersion()
    {
        return $this->operating_system_minimum_version;
    }
    public function setOperatingSystemMaximumVersion($a_val)
    {
        $this->operating_system_maximum_version = $a_val;
    }
    public function getOperatingSystemMaximumVersion()
    {
        return $this->operating_system_maximum_version;
    }
    public function setBrowserName($a_val)
    {
        switch ($a_val) {
            case 'Any':
            case 'NetscapeCommunicator':
            case 'MS-InternetExplorer':
            case 'Opera':
            case 'Amaya':
            case 'Mozilla':
                $this->browser_name = $a_val;
                return true;

            default:
                return false;
        }
    }
    public function getBrowserName()
    {
        return $this->browser_name;
    }
    public function setBrowserMinimumVersion($a_val)
    {
        $this->browser_minimum_version = $a_val;
    }
    public function getBrowserMinimumVersion()
    {
        return $this->browser_minimum_version;
    }
    public function setBrowserMaximumVersion($a_val)
    {
        $this->browser_maximum_version = $a_val;
    }
    public function getBrowserMaximumVersion()
    {
        return $this->browser_maximum_version;
    }

    public function save()
    {
        if ($this->db->autoExecute(
            'il_meta_requirement',
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
                'il_meta_requirement',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_requirement_id = " . $ilDB->quote($this->getMetaId())
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
            $query = "DELETE FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $ilDB->quote($this->getMetaId());
            
            $this->db->query($query);
            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> $this->getRBACId(),
                     'obj_id'	=> $this->getObjId(),
                     'obj_type'	=> ilUtil::prepareDBString($this->getObjType()),
                     'parent_type' => $this->getParentType(),
                     'parent_id' => $this->getParentId(),
                     'operating_system_name'	=> ilUtil::prepareDBString($this->getOperatingSystemName()),
                     'operating_system_minimum_version' => ilUtil::prepareDBString($this->getOperatingSystemMinimumVersion()),
                     'operating_system_maximum_version' => ilUtil::prepareDBString($this->getOperatingSystemMaximumVersion()),
                     'browser_name'	=> ilUtil::prepareDBString($this->getBrowserName()),
                     'browser_minimum_version' => ilUtil::prepareDBString($this->getBrowserMinimumVersion()),
                     'browser_maximum_version' => ilUtil::prepareDBString($this->getBrowserMaximumVersion()),
                     'or_composite_id' => $this->getOrCompositeId());
    }

    public function read()
    {
        global $ilDB;
                
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $ilDB->quote($this->getMetaId());

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setOperatingSystemName(ilUtil::stripSlashes($row->operating_system_name));
                $this->setOperatingSystemMinimumVersion(ilUtil::stripSlashes($row->operating_system_minimum_version));
                $this->setOperatingSystemMaximumVersion(ilUtil::stripSlashes($row->operating_system_maximum_version));
                $this->setBrowserName(ilUtil::stripSlashes($row->browser_name));
                $this->setBrowserMinimumVersion(ilUtil::stripSlashes($row->browser_minimum_version));
                $this->setBrowserMaximumVersion(ilUtil::stripSlashes($row->browser_maximum_version));
                $this->setOrCompositeId(ilUtil::stripSlashes($row->or_composite_id));
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
        $writer->xmlStartTag('Requirement');
        $writer->xmlStartTag('Type');
            
        if (strlen($this->getOperatingSystemName())) {
            $writer->xmlElement('OperatingSystem', array('Name' => $this->getOperatingSystemName(),
                                                        'MinimumVersion' => $this->getOperatingSystemMinimumVersion(),
                                                        'MaximumVersion' => $this->getOperatingSystemMaximumVersion()));
        } else {
            $writer->xmlElement('Browser', array('Name' => $this->getBrowserName(),
                                                'MinimumVersion' => $this->getBrowserMinimumVersion(),
                                                'MaximumVersion' => $this->getBrowserMaximumVersion()));
        }
        $writer->xmlEndTag('Type');
        $writer->xmlEndTag('Requirement');
    }


    // STATIC
    public function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type, $a_or_composite_id = 0)
    {
        global $ilDB;

        $query = "SELECT meta_requirement_id FROM il_meta_requirement " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id) . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id) . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type) . " " .
            "AND or_composite_id = " . $ilDB->quote($a_or_composite_id);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_requirement_id;
        }
        return $ids ? $ids : array();
    }
}
