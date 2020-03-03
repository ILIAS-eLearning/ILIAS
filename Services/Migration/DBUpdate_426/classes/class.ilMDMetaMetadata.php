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
* Meta Data class (element meta_data)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDMetaMetadata extends ilMDBase
{
    // SUBELEMENTS
    public function &getIdentifierIds()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDIdentifier.php';

        return ilMDIdentifier::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }
    public function &getIdentifier($a_identifier_id)
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDIdentifier.php';
        
        if (!$a_identifier_id) {
            return false;
        }
        $ide = new ilMDIdentifier();
        $ide->setMetaId($a_identifier_id);

        return $ide;
    }
    public function &addIdentifier()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDIdentifier.php';

        $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $ide->setParentId($this->getMetaId());
        $ide->setParentType('meta_meta_data');

        return $ide;
    }
    
    public function &getContributeIds()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDContribute.php';

        return ilMDContribute::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }
    public function &getContribute($a_contribute_id)
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDContribute.php';
        
        if (!$a_contribute_id) {
            return false;
        }
        $con = new ilMDContribute();
        $con->setMetaId($a_contribute_id);

        return $con;
    }
    public function &addContribute()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDContribute.php';

        $con = new ilMDContribute($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $con->setParentId($this->getMetaId());
        $con->setParentType('meta_meta_data');

        return $con;
    }



    // SET/GET
    public function setMetaDataScheme($a_val)
    {
        $this->meta_data_scheme = $a_val;
    }
    public function getMetaDataScheme()
    {
        // Fixed attribute
        return 'LOM v 1.0';
    }
    public function setLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->language = $lng_obj;
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
        if ($this->db->autoExecute(
            'il_meta_meta_data',
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
                'il_meta_meta_data',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_meta_data_id = " . $ilDB->quote($this->getMetaId())
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
            $query = "DELETE FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $ilDB->quote($this->getMetaId());
            
            $this->db->query($query);
            

            foreach ($this->getIdentifierIds() as $id) {
                $ide = $this->getIdentifier($id);
                $ide->delete();
            }
        
            foreach ($this->getContributeIds() as $id) {
                $con = $this->getContribute($id);
                $con->delete();
            }
            return true;
        }

        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> $this->getRBACId(),
                     'obj_id'	=> $this->getObjId(),
                     'obj_type'	=> ilUtil::prepareDBString($this->getObjType()),
                     'meta_data_scheme'	=> ilUtil::prepareDBString($this->getMetaDataScheme()),
                     'language' => ilUtil::prepareDBString($this->getLanguageCode()));
    }

    public function read()
    {
        global $ilDB;
                
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';


        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $ilDB->quote($this->getMetaId());

        
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setMetaDataScheme(ilUtil::stripSlashes($row->meta_data_scheme));
                $this->setLanguage(new ilMDLanguageItem($row->language));
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
        if ($this->getMigration/DBUpdate_426Scheme()) {
            $attr['Metadata-Scheme'] = $this->getMigration/DBUpdate_426Scheme();
        }
        if ($this->getLanguageCode()) {
            $attr['Language'] = $this->getLanguageCode();
        }
        $writer->xmlStartTag('Meta-Metadata', $attr ? $attr : null);

        // ELEMENT IDENTIFIER
        foreach ($this->getIdentifierIds() as $id) {
            $ide =&$this->getIdentifier($id);
            $ide->toXML($writer);
        }
        
        // ELEMETN Contribute
        foreach ($this->getContributeIds() as $id) {
            $con =&$this->getContribute($id);
            $con->toXML($writer);
        }

        $writer->xmlEndTag('Meta-Metadata');
    }

    // STATIC
    public function _getId($a_rbac_id, $a_obj_id)
    {
        global $ilDB;

        $query = "SELECT meta_meta_data_id FROM il_meta_meta_data " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->meta_meta_data_id;
        }
        return false;
    }
}
