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
* Meta Data class (element typicalagerange)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTypicalAgeRange extends ilMDBase
{
    // SET/GET
    public function setTypicalAgeRange($a_typical_age_range)
    {
        $this->typical_age_range = $a_typical_age_range;
    }
    public function getTypicalAgeRange()
    {
        return $this->typical_age_range;
    }
    public function setTypicalAgeRangeLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->typical_age_range_language = $lng_obj;
        }
    }
    public function &getTypicalAgeRangeLanguage()
    {
        return is_object($this->typical_age_range_language) ? $this->typical_age_range_language : false;
    }
    public function getTypicalAgeRangeLanguageCode()
    {
        return is_object($this->typical_age_range_language) ? $this->typical_age_range_language->getLanguageCode() : false;
    }

    public function setTypicalAgeRangeMinimum($a_min)
    {
        $this->typical_age_range_minimum = $a_min;
    }
    public function getTypicalAgeRangeMinimum()
    {
        return $this->typical_age_range_minimum;
    }
    public function setTypicalAgeRangeMaximum($a_max)
    {
        $this->typical_age_range_maximum = $a_max;
    }
    public function getTypicalAgeRangeMaximum()
    {
        return $this->typical_age_range_maximum;
    }


    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_tar_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_tar'));
        
        if ($this->db->insert('il_meta_tar', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->__parseTypicalAgeRange();
        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_tar',
                $this->__getFields(),
                array("meta_tar_id" => array('integer',$this->getMetaId()))
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
            $query = "DELETE FROM il_meta_tar " .
                "WHERE meta_tar_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> array('integer',$this->getRBACId()),
                     'obj_id'	=> array('integer',$this->getObjId()),
                     'obj_type'	=> array('text',$this->getObjType()),
                     'parent_type' => array('text',$this->getParentType()),
                     'parent_id' => array('integer',$this->getParentId()),
                     'typical_age_range'	=> array('text',$this->getTypicalAgeRange()),
                     'tar_language' => array('text',$this->getTypicalAgeRangeLanguageCode()),
                     'tar_min' => array('text',$this->getTypicalAgeRangeMinimum()),
                     'tar_max' => array('text',$this->getTypicalAgeRangeMaximum()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_tar " .
                "WHERE meta_tar_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setTypicalAgeRange($row->typical_age_range);
                $this->setTypicalAgeRangeLanguage(new ilMDLanguageItem($row->tar_language));
                $this->setTypicalAgeRangeMinimum($row->tar_min);
                $this->setTypicalAgeRangeMaximum($row->tar_max);
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
        $writer->xmlElement(
            'TypicalAgeRange',
            array('Language' => $this->getTypicalAgeRangeLanguageCode()
                                                    ? $this->getTypicalAgeRangeLanguageCode()
                                                    : 'en'),
            $this->getTypicalAgeRange()
        );
    }


    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_tar_id FROM il_meta_tar " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');
            
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_tar_id;
        }
        return $ids ? $ids : array();
    }

    // PRIVATE
    public function __parseTypicalAgeRange()
    {
        if (preg_match("/\s*(\d*)\s*(-?)\s*(\d*)/", $this->getTypicalAgeRange(), $matches)) {
            if (!$matches[2] and !$matches[3]) {
                $min = $max = $matches[1];
            } elseif ($matches[2] and !$matches[3]) {
                $min = $matches[1];
                $max = 99;
            } else {
                $min = $matches[1];
                $max = $matches[3];
            }
            $this->setTypicalAgeRangeMaximum($max);
            $this->setTypicalAgeRangeMinimum($min);

            return true;
        }

        if (!$this->getTypicalAgeRange()) {
            $this->setTypicalAgeRangeMinimum(-1);
            $this->setTypicalAgeRangeMaximum(-1);
        }
        return true;
    }
}
