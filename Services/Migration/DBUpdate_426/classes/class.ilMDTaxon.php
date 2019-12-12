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
* Meta Data class (element taxon)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTaxon extends ilMDBase
{
    // SET/GET
    public function setTaxon($a_taxon)
    {
        $this->taxon = $a_taxon;
    }
    public function getTaxon()
    {
        return $this->taxon;
    }
    public function setTaxonLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->taxon_language = $lng_obj;
        }
    }
    public function &getTaxonLanguage()
    {
        return is_object($this->taxon_language) ? $this->taxon_language : false;
    }
    public function getTaxonLanguageCode()
    {
        return is_object($this->taxon_language) ? $this->taxon_language->getLanguageCode() : false;
    }
    public function setTaxonId($a_taxon_id)
    {
        $this->taxon_id = $a_taxon_id;
    }
    public function getTaxonId()
    {
        return $this->taxon_id;
    }
    

    public function save()
    {
        if ($this->db->autoExecute(
            'il_meta_taxon',
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
                'il_meta_taxon',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_taxon_id = " . $ilDB->quote($this->getMetaId())
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_taxon " .
                "WHERE meta_taxon_id = " . $ilDB->quote($this->getMetaId());
            
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
                     'taxon'	=> ilUtil::prepareDBString($this->getTaxon()),
                     'taxon_language' => ilUtil::prepareDBString($this->getTaxonLanguageCode()),
                     'taxon_id'	=> ilUtil::prepareDBString($this->getTaxonId()));
    }

    public function read()
    {
        global $ilDB;
        
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_taxon " .
                "WHERE meta_taxon_id = " . $ilDB->quote($this->getMetaId());

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setTaxon(ilUtil::stripSlashes($row->taxon));
                $this->taxon_language = new ilMDLanguageItem($row->taxon_language);
                $this->setTaxonId(ilUtil::stripSlashes($row->taxon_id));
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
        $writer->xmlElement('Taxon', array('Language' => $this->getTaxonLanguageCode(),
                                          'Id'		 => $this->getTaxonId()), $this->getTaxon());
    }


    // STATIC
    public function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $ilDB;

        $query = "SELECT meta_taxon_id FROM il_meta_taxon " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id) . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id) . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type);


        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_taxon_id;
        }
        return $ids ? $ids : array();
    }
}
