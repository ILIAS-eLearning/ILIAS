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
* Meta Data class (element classification)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDClassification extends ilMDBase
{
    // METHODS OF CLIENT OBJECTS (TaxonPath, Keyword)
    public function &getTaxonPathIds()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDTaxonPath.php';

        return ilMDTaxonPath::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }
    public function &getTaxonPath($a_taxon_path_id)
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDTaxonPath.php';

        if (!$a_taxon_path_id) {
            return false;
        }
        $tax = new ilMDTaxonPath();
        $tax->setMetaId($a_taxon_path_id);

        return $tax;
    }
    public function &addTaxonPath()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDTaxonPath.php';

        $tax = new ilMDTaxonPath($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $tax->setParentId($this->getMetaId());
        $tax->setParentType('meta_classification');

        return $tax;
    }

    public function &getKeywordIds()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDKeyword.php';

        return ilMDKeyword::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }
    public function &getKeyword($a_keyword_id)
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDKeyword.php';
        
        if (!$a_keyword_id) {
            return false;
        }
        $key = new ilMDKeyword();
        $key->setMetaId($a_keyword_id);

        return $key;
    }
    public function &addKeyword()
    {
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDKeyword.php';

        $key = new ilMDKeyword($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $key->setParentId($this->getMetaId());
        $key->setParentType('meta_classification');

        return $key;
    }

    // SET/GET
    public function setPurpose($a_purpose)
    {
        switch ($a_purpose) {
            case 'Discipline':
            case 'Idea':
            case 'Prerequisite':
            case 'EducationalObjective':
            case 'AccessibilityRestrictions':
            case 'EducationalLevel':
            case 'SkillLevel':
            case 'SecurityLevel':
            case 'Competency':
                $this->purpose = $a_purpose;
                return true;

            default:
                return false;
        }
    }
    public function getPurpose()
    {
        return $this->purpose;
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
            $this->description_language =&$lng_obj;
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
            'il_meta_classification',
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
                'il_meta_classification',
                $this->__getFields(),
                ilDBConstants::AUTOQUERY_UPDATE,
                "meta_classification_id = " . $ilDB->quote($this->getMetaId())
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
            $query = "DELETE FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $ilDB->quote($this->getMetaId());
            
            $this->db->query($query);

            foreach ($this->getTaxonPathIds() as $id) {
                $tax =&$this->getTaxonPath($id);
                $tax->delete();
            }
            foreach ($this->getKeywordIds() as $id) {
                $key =&$this->getKeyword($id);
                $key->delete();
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
                     'purpose'	=> ilUtil::prepareDBString($this->getPurpose()),
                     'description' => ilUtil::prepareDBString($this->getDescription()),
                     'description_language' => ilUtil::prepareDBString($this->getDescriptionLanguageCode()));
    }

    public function read()
    {
        global $ilDB;
        
        include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $ilDB->quote($this->getMetaId());

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setPurpose(ilUtil::stripSlashes($row->purpose));
                $this->setDescription(ilUtil::stripSlashes($row->description));
                $this->description_language = new ilMDLanguageItem($row->description_language);
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
        $writer->xmlStartTag('Classification', array('Purpose' => $this->getPurpose()));

        // Taxon Path
        foreach ($this->getTaxonPathIds() as $id) {
            $tax =&$this->getTaxonPath($id);
            $tax->toXML($writer);
        }
        // Description
        $writer->xmlElement('Description', array('Language' => $this->getDescriptionLanguageCode()), $this->getDescription());
        
        // Keyword
        foreach ($this->getKeywordIds() as $id) {
            $key =&$this->getKeyword($id);
            $key->toXML($writer);
        }
        $writer->xmlEndTag('Classification');
    }

                

    // STATIC
    public function _getIds($a_rbac_id, $a_obj_id)
    {
        global $ilDB;

        $query = "SELECT meta_classification_id FROM il_meta_classification " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id);


        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_classification_id;
        }
        return $ids ? $ids : array();
    }
}
