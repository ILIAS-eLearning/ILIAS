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
 * Meta Data class (element taxon)
 * @package ilias-core
 * @version $Id$
 */
class ilMDTaxon extends ilMDBase
{
    private string $taxon = '';
    private ?ilMDLanguageItem $taxon_language = null;
    private string $taxon_id = '';

    // SET/GET
    public function setTaxon(string $a_taxon): void
    {
        $this->taxon = $a_taxon;
    }

    public function getTaxon(): string
    {
        return $this->taxon;
    }

    public function setTaxonLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->taxon_language = $lng_obj;
    }

    public function getTaxonLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->taxon_language) ? $this->taxon_language : null;
    }

    public function getTaxonLanguageCode(): string
    {
        return is_object($this->taxon_language) ? $this->taxon_language->getLanguageCode() : '';
    }

    public function setTaxonId(string $a_taxon_id): void
    {
        $this->taxon_id = $a_taxon_id;
    }

    public function getTaxonId(): string
    {
        return $this->taxon_id;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_taxon_id'] = array('integer', $next_id = $this->db->nextId('il_meta_taxon'));

        if ($this->db->insert('il_meta_taxon', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_taxon',
            $this->__getFields(),
            array("meta_taxon_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_taxon " .
                "WHERE meta_taxon_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $this->db->query($query);

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
            'taxon' => array('text', $this->getTaxon()),
            'taxon_language' => array('text', $this->getTaxonLanguageCode()),
            'taxon_id' => array('text', $this->getTaxonId())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_taxon " .
                "WHERE meta_taxon_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setTaxon($row->taxon);
                $this->taxon_language = new ilMDLanguageItem($row->taxon_language);
                $this->setTaxonId($row->taxon_id);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $random = new \ilRandom();
        $writer->xmlElement(
            'Taxon',
            array(
                'Language' => $this->getTaxonLanguageCode() ?: 'en',
                'Id' => $this->getTaxonId() ?: ("ID" . $random->int())
            ),
            $this->getTaxon()
        );
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_taxon_id FROM il_meta_taxon " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_taxon_id;
        }
        return $ids;
    }
}
