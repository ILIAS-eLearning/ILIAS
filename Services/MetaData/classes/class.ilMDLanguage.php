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
* Meta Data class (element language)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDLanguage extends ilMDBase
{
    /**
     * Lookup first language
     *
     * @access public
     * @static
     *
     * @param
     */
    public static function _lookupFirstLanguage($a_rbac_id, $a_obj_id, $a_obj_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $lang = '';
        $query = "SELECT language FROM il_meta_language " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_obj_type, 'text') . " " .
            "AND parent_type = 'meta_general' " .
            "ORDER BY meta_language_id ";
        $ilDB->setLimit(1);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lang = $row->language;
        }
        return $lang;
    }

    // SET/GET
    public function setLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->language = &$lng_obj;
        }
    }
    public function &getLanguage()
    {
        return is_object($this->language) ? $this->language : false;
    }
    public function getLanguageCode()
    {
        return is_object($this->language) ? $this->language->getLanguageCode() : false;
    }

    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_language_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_language'));
        
        if ($this->db->insert('il_meta_language', $fields)) {
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
                'il_meta_language',
                $this->__getFields(),
                array("meta_language_id" => array('integer',$this->getMetaId()))
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
            $query = "DELETE FROM il_meta_language " .
                "WHERE meta_language_id = " . $ilDB->quote($this->getMetaId(), 'integer');
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
                     'language' => array('text',$this->getLanguageCode()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_language " .
                "WHERE meta_language_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setLanguage(new ilMDLanguageItem($row->language));
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
            'Language',
            array('Language' => $this->getLanguageCode() ?
                                             $this->getLanguageCode() :
                                             'en'),
            $this->getLanguage()
        );
    }


    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_language_id FROM il_meta_language " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_language_id;
        }
        return $ids ? $ids : array();
    }
}
