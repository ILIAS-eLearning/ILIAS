<?php declare(strict_types=1);
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
 * @package ilias-core
 * @version $Id$
 */
class ilMDClassification extends ilMDBase
{
    private string $purpose = '';
    private string $description = '';
    private ?ilMDLanguageItem $description_language = null;

    // METHODS OF CLIENT OBJECTS (TaxonPath, Keyword)

    /**
     * @return int[]
     */
    public function getTaxonPathIds() : array
    {
        return ilMDTaxonPath::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }

    public function getTaxonPath(int $a_taxon_path_id) : ?ilMDTaxonPath
    {
        if (!$a_taxon_path_id) {
            return null;
        }
        $tax = new ilMDTaxonPath();
        $tax->setMetaId($a_taxon_path_id);

        return $tax;
    }

    public function addTaxonPath() : ilMDTaxonPath
    {
        $tax = new ilMDTaxonPath($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $tax->setParentId($this->getMetaId());
        $tax->setParentType('meta_classification');

        return $tax;
    }

    public function getKeywordIds() : ?array
    {
        return ilMDKeyword::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_classification');
    }

    public function getKeyword(int $a_keyword_id) : ?ilMDKeyword
    {
        if (!$a_keyword_id) {
            return null;
        }
        $key = new ilMDKeyword();
        $key->setMetaId($a_keyword_id);

        return $key;
    }

    public function addKeyword() : ilMDKeyword
    {
        $key = new ilMDKeyword($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $key->setParentId($this->getMetaId());
        $key->setParentType('meta_classification');

        return $key;
    }

    // SET/GET
    public function setPurpose(string $a_purpose) : bool
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

    public function getPurpose() : string
    {
        return $this->purpose;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescriptionLanguage(ilMDLanguageItem $lng_obj) : void
    {
        $this->description_language = $lng_obj;
    }

    public function getDescriptionLanguage() : ?ilMDLanguageItem
    {
        return is_object($this->description_language) ? $this->description_language : null;
    }

    public function getDescriptionLanguageCode() : string
    {
        return is_object($this->description_language) ? $this->description_language->getLanguageCode() : '';
    }

    public function save() : int
    {
        $fields = $this->__getFields();
        $fields['meta_classification_id'] = array('integer', $next_id = $this->db->nextId('il_meta_classification'));

        if ($this->db->insert('il_meta_classification', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_classification',
            $this->__getFields(),
            ["meta_classification_id" => ['integer', $this->getMetaId()]]
        );
    }

    public function delete() : bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields() : array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'purpose' => array('text', $this->getPurpose()),
            'description' => array('text', $this->getDescription()),
            'description_language' => array('text', $this->getDescriptionLanguageCode())
        );
    }

    public function read() : bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_classification " .
                "WHERE meta_classification_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setPurpose($row->purpose);
                $this->setDescription($row->description);
                $this->description_language = new ilMDLanguageItem($row->description_language);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag('Classification', array(
            'Purpose' => $this->getPurpose() ?: 'Idea'
        ));

        // Taxon Path
        $taxs = $this->getTaxonPathIds();
        foreach ($taxs as $id) {
            $tax = $this->getTaxonPath($id);
            $tax->toXML($writer);
        }
        if (!count($taxs)) {
            $tax = new ilMDTaxonPath($this->getRBACId(), $this->getObjId());
            $tax->toXML($writer);
        }

        // Description
        $writer->xmlElement(
            'Description',
            array(
                'Language' => $this->getDescriptionLanguageCode() ?: 'en'
            ),
            $this->getDescription()
        );

        // Keyword
        $keys = $this->getKeywordIds();
        foreach ($keys as $id) {
            $key = $this->getKeyword($id);
            $key->toXML($writer);
        }
        if (!count($keys)) {
            $key = new ilMDKeyword($this->getRBACId(), $this->getObjId());
            $key->toXML($writer);
        }
        $writer->xmlEndTag('Classification');
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_classification_id FROM il_meta_classification " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_classification_id;
        }
        return $ids;
    }
}
