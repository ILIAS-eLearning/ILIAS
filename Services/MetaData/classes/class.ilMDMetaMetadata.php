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
 * Meta Data class (element meta_data)
 * @package ilias-core
 * @version $Id$
 */
class ilMDMetaMetadata extends ilMDBase
{
    private string $meta_data_scheme = 'LOM v 1.0';
    private ?ilMDLanguageItem $language = null;

    /**
     * @return array<string, string>
     */
    public function getPossibleSubelements(): array
    {
        $subs['Identifier'] = 'meta_identifier';
        $subs['Contribute'] = 'meta_contribute';

        return $subs;
    }

    // SUBELEMENTS

    /**
     * @return int[]
     */
    public function getIdentifierIds(): array
    {
        return ilMDIdentifier::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }

    public function getIdentifier(int $a_identifier_id): ?ilMDIdentifier
    {
        if (!$a_identifier_id) {
            return null;
        }
        $ide = new ilMDIdentifier();
        $ide->setMetaId($a_identifier_id);

        return $ide;
    }

    public function addIdentifier(): ilMDIdentifier
    {
        $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $ide->setParentId($this->getMetaId());
        $ide->setParentType('meta_meta_data');

        return $ide;
    }

    /**
     * @return int[]
     */
    public function getContributeIds(): array
    {
        return ilMDContribute::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }

    public function getContribute(int $a_contribute_id): ?ilMDContribute
    {
        if (!$a_contribute_id) {
            return null;
        }
        $con = new ilMDContribute();
        $con->setMetaId($a_contribute_id);

        return $con;
    }

    public function addContribute(): ilMDContribute
    {
        $con = new ilMDContribute($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $con->setParentId($this->getMetaId());
        $con->setParentType('meta_meta_data');

        return $con;
    }

    // SET/GET
    //TODO: check fixed attribute
    public function setMetaDataScheme(string $a_val): void
    {
        $this->meta_data_scheme = $a_val;
    }

    public function getMetaDataScheme(): string
    {
        // Fixed attribute
        return 'LOM v 1.0';
    }

    public function setLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->language = $lng_obj;
    }

    public function getLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->language) ? $this->language : null;
    }

    public function getLanguageCode(): string
    {
        return is_object($this->language) ? $this->language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_meta_data_id'] = array('integer', $next_id = $this->db->nextId('il_meta_meta_data'));

        if ($this->db->insert('il_meta_meta_data', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_meta_data',
            $this->__getFields(),
            array("meta_meta_data_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'meta_data_scheme' => array('text', $this->getMetaDataScheme()),
            'language' => array('text', $this->getLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setMetaDataScheme($row->meta_data_scheme);
                $this->setLanguage(new ilMDLanguageItem($row->language));
            }
            return true;
        }
        return false;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $attr = null;
        if ($this->getMetaDataScheme()) {
            $attr['MetadataScheme'] = $this->getMetaDataScheme();
        }
        if ($this->getLanguageCode()) {
            $attr['Language'] = $this->getLanguageCode();
        }
        $writer->xmlStartTag('Meta-Metadata', $attr);

        // ELEMENT IDENTIFIER
        $identifiers = $this->getIdentifierIds();
        foreach ($identifiers as $id) {
            $ide = $this->getIdentifier($id);
            $ide->toXML($writer);
        }
        if (!count($identifiers)) {
            $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId());
            $ide->toXML($writer);
        }

        // ELEMETN Contribute
        $contributes = $this->getContributeIds();
        foreach ($contributes as $id) {
            $con = $this->getContribute($id);
            $con->toXML($writer);
        }
        if (!count($contributes)) {
            $con = new ilMDContribute($this->getRBACId(), $this->getObjId());
            $con->toXML($writer);
        }

        $writer->xmlEndTag('Meta-Metadata');
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_meta_data_id FROM il_meta_meta_data " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_meta_data_id;
        }
        return 0;
    }
}
