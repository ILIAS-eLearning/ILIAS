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
* Meta Data class (element general)
*
* @author Stefan Meyer <meyer@leifos.com>
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDGeneral extends ilMDBase
{
    public function getPossibleSubelements()
    {
        $subs['Keyword'] = 'meta_keyword';
        $subs['Language'] = 'meta_language';
        $subs['Identifier'] = 'meta_identifier';
        $subs['Description'] = 'meta_description';

        return $subs;
    }


    // Subelements (Identifier, Language, Description, Keyword)
    public function &getIdentifierIds()
    {
        include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

        return ilMDIdentifier::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_general');
    }
    public function &getIdentifier($a_identifier_id)
    {
        include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';
        
        if (!$a_identifier_id) {
            return false;
        }
        $ide = new ilMDIdentifier();
        $ide->setMetaId($a_identifier_id);
        
        return $ide;
    }
    public function &addIdentifier()
    {
        include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

        $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $ide->setParentId($this->getMetaId());
        $ide->setParentType('meta_general');

        return $ide;
    }
    public function &getLanguageIds()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

        return ilMDLanguage::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_general');
    }
    public function &getLanguage($a_language_id)
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

        if (!$a_language_id) {
            return false;
        }
        $lan = new ilMDLanguage();
        $lan->setMetaId($a_language_id);

        return $lan;
    }
    public function &addLanguage()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguage.php';
        
        $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $lan->setParentId($this->getMetaId());
        $lan->setParentType('meta_general');

        return $lan;
    }
    public function &getDescriptionIds()
    {
        include_once 'Services/MetaData/classes/class.ilMDDescription.php';

        return ilMDDescription::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_general');
    }
    public function &getDescription($a_description_id)
    {
        include_once 'Services/MetaData/classes/class.ilMDDescription.php';


        if (!$a_description_id) {
            return false;
        }
        $des = new ilMDDescription();
        $des->setMetaId($a_description_id);

        return $des;
    }
    public function &addDescription()
    {
        include_once 'Services/MetaData/classes/class.ilMDDescription.php';

        $des = new ilMDDescription($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $des->setParentId($this->getMetaId());
        $des->setParentType('meta_general');

        return $des;
    }
    public function &getKeywordIds()
    {
        include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

        return ilMDKeyword::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_general');
    }
    public function &getKeyword($a_keyword_id)
    {
        include_once 'Services/MetaData/classes/class.ilMDKeyword.php';
        
        if (!$a_keyword_id) {
            return false;
        }
        $key = new ilMDKeyword();
        $key->setMetaId($a_keyword_id);

        return $key;
    }
    public function &addKeyword()
    {
        include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

        $key = new ilMDKeyword($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $key->setParentId($this->getMetaId());
        $key->setParentType('meta_general');

        return $key;
    }



    // SET/GET
    public function setStructure($a_structure)
    {
        switch ($a_structure) {
            case 'Atomic':
            case 'Collection':
            case 'Networked':
            case 'Hierarchical':
            case 'Linear':
                $this->structure = $a_structure;
                return true;

            default:
                return false;
        }
    }
    public function getStructure()
    {
        return $this->structure;
    }
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitleLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->title_language = $lng_obj;
        }
    }
    public function &getTitleLanguage()
    {
        return is_object($this->title_language) ? $this->title_language : false;
    }
    public function getTitleLanguageCode()
    {
        return is_object($this->title_language) ? $this->title_language->getLanguageCode() : false;
    }

    public function setCoverage($a_coverage)
    {
        $this->coverage = $a_coverage;
    }
    public function getCoverage()
    {
        return $this->coverage;
    }

    public function setCoverageLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->coverage_language = $lng_obj;
        }
    }
    public function &getCoverageLanguage()
    {
        return is_object($this->coverage_language) ? $this->coverage_language : false;
    }
    public function getCoverageLanguageCode()
    {
        return is_object($this->coverage_language) ? $this->coverage_language->getLanguageCode() : false;
    }


    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_general_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_general'));

        $this->log->debug("Insert General " . print_r($fields, true));
        $this->log->logStack(ilLogLevel::DEBUG);
        //ilUtil::printBacktrace(10);

        if ($this->db->insert('il_meta_general', $fields)) {
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
                'il_meta_general',
                $this->__getFields(),
                array("meta_general_id" => array('integer',$this->getMetaId()))
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
        
        if (!$this->getMetaId()) {
            return false;
        }
        // Identifier
        foreach ($this->getIdentifierIds() as $id) {
            $ide = $this->getIdentifier($id);
            $ide->delete();
        }

        // Language
        foreach ($this->getLanguageIds() as $id) {
            $lan = $this->getLanguage($id);
            $lan->delete();
        }

        // Description
        foreach ($this->getDescriptionIds() as $id) {
            $des = $this->getDescription($id);
            $des->delete();
        }

        // Keyword
        foreach ($this->getKeywordIds() as $id) {
            $key = $this->getKeyword($id);
            $key->delete();
        }
        
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_general " .
                "WHERE meta_general_id = " . $ilDB->quote($this->getMetaId(), 'integer');
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
                     'general_structure' => array('text',$this->getStructure()),
                     'title' => array('text',$this->getTitle()),
                     'title_language' => array('text',$this->getTitleLanguageCode()),
                     'coverage' => array('text',$this->getCoverage()),
                     'coverage_language' => array('text',$this->getCoverageLanguageCode()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_general " .
                "WHERE meta_general_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setStructure($row->general_structure);
                $this->setTitle($row->title);
                $this->setTitleLanguage(new ilMDLanguageItem($row->title_language));
                $this->setCoverage($row->coverage);
                $this->setCoverageLanguage(new ilMDLanguageItem($row->coverage_language));
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
        $writer->xmlStartTag('General', array('Structure' => $this->getStructure() ?
                                             $this->getStructure() :
                                             'Atomic'));
                                             

        // Identifier
        $first = true;
        $identifiers = array();
        $identifiers = $this->getIdentifierIds();
        foreach ($identifiers as $id) {
            $ide = &$this->getIdentifier($id);
            $ide->setExportMode($this->getExportMode());
            $ide->toXML($writer);
            $first = false;
        }
        if (!count($identifiers)) {
            include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';
            $ide = new ilMDIdentifier(
                $this->getRBACId(),
                $this->getObjId(),
                $this->getObjType()
            );		// added type, alex, 31 Oct 2007
            $ide->setExportMode(true);
            $ide->toXML($writer, true);
        }
        
        // Title
        $writer->xmlElement(
            'Title',
            array('Language' => $this->getTitleLanguageCode() ?
                                          $this->getTitleLanguageCode() :
                                          'en'),
            $this->getTitle()
        );

        // Language
        $languages = $this->getLanguageIds();
        foreach ($languages as $id) {
            $lan = &$this->getLanguage($id);
            $lan->toXML($writer);
        }
        if (!count($languages)) {
            // Default
            include_once 'Services/MetaData/classes/class.ilMDLanguage.php';
            $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId());
            $lan->toXML($writer);
        }

        // Description
        $descriptions = $this->getDescriptionIds();
        foreach ($descriptions as $id) {
            $des = &$this->getDescription($id);
            $des->toXML($writer);
        }
        if (!count($descriptions)) {
            // Default
            include_once 'Services/MetaData/classes/class.ilMDDescription.php';
            $des = new ilMDDescription($this->getRBACId(), $this->getObjId());
            $des->toXML($writer);
        }
            

        // Keyword
        $keywords = $this->getKeywordIds();
        foreach ($keywords as $id) {
            $key = &$this->getKeyword($id);
            $key->toXML($writer);
        }
        if (!count($keywords)) {
            // Default
            include_once 'Services/MetaData/classes/class.ilMDKeyword.php';
            $key = new ilMDKeyword($this->getRBACId(), $this->getObjId());
            $key->toXML($writer);
        }
        
        // Copverage
        if (strlen($this->getCoverage())) {
            $writer->xmlElement(
                'Coverage',
                array('Language' => $this->getCoverageLanguageCode() ?
                                                 $this->getCoverageLanguageCode() :
                                                 'en'),
                $this->getCoverage()
            );
        }
        $writer->xmlEndTag('General');
    }

                

    // STATIC
    public static function _getId($a_rbac_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_general_id FROM il_meta_general " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');


        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->meta_general_id;
        }
        return false;
    }
}
