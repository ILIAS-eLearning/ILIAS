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
* Meta Data class (element rights)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDRights extends ilMDBase
{
    // SET/GET
    public function setCosts($a_costs)
    {
        switch ($a_costs) {
            case 'Yes':
            case 'No':
                $this->costs = $a_costs;
                return true;

            default:
                return false;
        }
    }
    public function getCosts()
    {
        return $this->costs;
    }
    public function setCopyrightAndOtherRestrictions($a_caor)
    {
        switch ($a_caor) {
            case 'Yes':
            case 'No':
                $this->caor = $a_caor;
                return true;

            default:
                return false;
        }
    }
    public function getCopyrightAndOtherRestrictions()
    {
        return $this->caor;
    }
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescriptionLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->description_language = $lng_obj;
        }
    }
    public function &getDescriptionLanguage()
    {
        return is_object($this->description_language) ? $this->description_language : false;
    }
    public function getDescriptionLanguageCode()
    {
        return is_object($this->description_language) ? $this->description_language->getLanguageCode() : false;
    }

    public function save()
    {
        if ($this->db->autoExecute(
            'il_meta_rights',
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
                'il_meta_rights',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_rights_id = " . $ilDB->quote($this->getMetaId())
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
            $query = "DELETE FROM il_meta_rights " .
                "WHERE meta_rights_id = " . $ilDB->quote($this->getMetaId());
            
            $this->db->query($query);
            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id' => $this->getRBACId(),
                     'obj_id' => $this->getObjId(),
                     'obj_type' => ilUtil::prepareDBString($this->getObjType()),
                     'costs' => ilUtil::prepareDBString($this->getCosts()),
                     'copyright_and_other_restrictions' => ilUtil::prepareDBString($this->getCopyrightAndOtherRestrictions()),
                     'description' => ilUtil::prepareDBString($this->getDescription()),
                     'description_language' => ilUtil::prepareDBString($this->getDescriptionLanguageCode()));
    }

    public function read()
    {
        global $ilDB;
        
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';


        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_rights " .
                "WHERE meta_rights_id = " . $ilDB->quote($this->getMetaId());

        
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setDescription(ilUtil::stripSlashes($row->description));
                $this->setDescriptionLanguage(new ilMDLanguageItem($row->description_language));
                $this->setCosts(ilUtil::stripSlashes($row->costs));
                $this->setCopyrightAndOtherRestrictions(ilUtil::stripSlashes($row->copyright_and_other_restrictions));
            }
            return true;
        }
        return false;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag('Rights', array('Costs' => $this->getCosts(),
                                            'CopyrightAndOtherRestrictions' => $this->getCopyrightAndOtherRestrictions()));
        $writer->xmlElement('Description', array('Language' => $this->getDescriptionLanguageCode()), $this->getDescription());
        $writer->xmlEndTag('Rights');
    }

    // STATIC
    public function _getId($a_rbac_id, $a_obj_id)
    {
        global $ilDB;

        $query = "SELECT meta_rights_id FROM il_meta_rights " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->meta_rights_id;
        }
        return false;
    }
}
