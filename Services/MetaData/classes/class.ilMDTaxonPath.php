<?php

declare(strict_types=1);
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
 * Meta Data class (element taxon_path)
 * @package ilias-core
 * @version $Id$
 */
class ilMDTaxonPath extends ilMDBase
{
    private string $source = '';
    private ?ilMDLanguageItem $source_language = null;

    // METHODS OF CHILD OBJECTS (Taxon)

    /**
     * @return int[]
     */
    public function getTaxonIds(): array
    {
        return ilMDTaxon::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_taxon_path');
    }

    public function getTaxon(int $a_taxon_id): ?ilMDTaxon
    {
        if (!$a_taxon_id) {
            return null;
        }
        $tax = new ilMDTaxon();
        $tax->setMetaId($a_taxon_id);

        return $tax;
    }

    public function addTaxon(): ilMDTaxon
    {
        $tax = new ilMDTaxon($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $tax->setParentId($this->getMetaId());
        $tax->setParentType('meta_taxon_path');

        return $tax;
    }

    // SET/GET
    public function setSource(string $a_source): void
    {
        $this->source = $a_source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSourceLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->source_language = $lng_obj;
    }

    public function getSourceLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->source_language) ? $this->source_language : null;
    }

    public function getSourceLanguageCode(): string
    {
        return is_object($this->source_language) ? $this->source_language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_taxon_path_id'] = array('integer', $next_id = $this->db->nextId('il_meta_taxon_path'));

        if ($this->db->insert('il_meta_taxon_path', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_taxon_path',
            $this->__getFields(),
            array("meta_taxon_path_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_taxon_path " .
                "WHERE meta_taxon_path_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

            foreach ($this->getTaxonIds() as $id) {
                $tax = $this->getTaxon($id);
                $tax->delete();
            }

            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'parent_type' => array('text', $this->getParentType()),
            'parent_id' => array('integer', $this->getParentId()),
            'source' => array('text', $this->getSource()),
            'source_language' => array('text', $this->getSourceLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_taxon_path " .
                "WHERE meta_taxon_path_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setSource($row->source);
                $this->source_language = new ilMDLanguageItem($row->source_language);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('TaxonPath');

        $writer->xmlElement(
            'Source',
            array(
                'Language' => $this->getSourceLanguageCode() ?: 'en'
            ),
            $this->getSource()
        );

        // Taxon
        $taxs = $this->getTaxonIds();
        foreach ($taxs as $id) {
            $tax = $this->getTaxon($id);
            $tax->toXML($writer);
        }
        if (!count($taxs)) {
            $tax = new ilMDTaxon($this->getRBACId(), $this->getObjId());
            $tax->toXML($writer);
        }

        $writer->xmlEndTag('TaxonPath');
    }

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_taxon_path_id FROM il_meta_taxon_path " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_taxon_path_id;
        }
        return $ids;
    }
}
