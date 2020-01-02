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
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTaxonPath.php';

        return ilMDTaxonPath::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }
    public function &getTaxonPath($a_taxon_path_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTaxonPath.php';

        if (!$a_taxon_path_id) {
            return false;
        }
        $tax = new ilMDTaxonPath();
        $tax->setMetaId($a_taxon_path_id);

        return $tax;
    }
    public function &addTaxonPath()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTaxonPath.php';

        $tax = new ilMDTaxonPath($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $tax->setParentId($this->getMetaId());
        $tax->setParentType('meta_classification');

        return $tax;
    }

    public function &getKeywordIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDKeyword.php';

        return ilMDKeyword::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }
    public function &getKeyword($a_keyword_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDKeyword.php';
        
        if (!$a_keyword_id) {
            return false;
        }
        $key = new ilMDKeyword();
        $key->setMetaId($a_keyword_id);

        return $key;
    }
    public function &addKeyword()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDKeyword.php';

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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_classification_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_classification'));
        
        if ($this->db->insert('il_meta_classification', $fields)) {
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
                'il_meta_classification',
                $this->__getFields(),
                array("meta_classification_id" => array('integer',$this->getMetaId()))
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
            $query = "DELETE FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);

            foreach ($this->getTaxonPathIds() as $id) {
                $tax = $this->getTaxonPath($id);
                $tax->delete();
            }
            foreach ($this->getKeywordIds() as $id) {
                $key = $this->getKeyword($id);
                $key->delete();
            }
            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> array('integer',$this->getRBACId()),
                     'obj_id'	=> array('integer',$this->getObjId()),
                     'obj_type'	=> array('text',$this->getObjType()),
                     'purpose'	=> array('text',$this->getPurpose()),
                     'description' => array('text',$this->getDescription()),
                     'description_language' => array('text',$this->getDescriptionLanguageCode()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setPurpose($row->purpose);
                $this->setDescription($row->description);
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
        $writer->xmlStartTag('Classification', array('Purpose' => $this->getPurpose()
                                                    ? $this->getPurpose()
                                                    : 'Idea'));

        // Taxon Path
        $taxs = $this->getTaxonPathIds();
        foreach ($taxs as $id) {
            $tax =&$this->getTaxonPath($id);
            $tax->toXML($writer);
        }
        if (!count($taxs)) {
            include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTaxonPath.php';
            $tax = new ilMDTaxonPath($this->getRBACId(), $this->getObjId());
            $tax->toXML($writer);
        }

        // Description
        $writer->xmlElement(
            'Description',
            array('Language' => $this->getDescriptionLanguageCode()
                                                ? $this->getDescriptionLanguageCode()
                                                : 'en'),
            $this->getDescription()
        );
        
        // Keyword
        $keys = $this->getKeywordIds();
        foreach ($keys as $id) {
            $key =&$this->getKeyword($id);
            $key->toXML($writer);
        }
        if (!count($keys)) {
            include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDKeyword.php';
            $key = new ilMDKeyword($this->getRBACId(), $this->getObjId());
            $key->toXML($writer);
        }
        $writer->xmlEndTag('Classification');
    }

                

    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_classification_id FROM il_meta_classification " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');


        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_classification_id;
        }
        return $ids ? $ids : array();
    }
}
