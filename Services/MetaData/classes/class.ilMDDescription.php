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
 * Meta Data class (element description)
 * @package ilias-core
 * @version $Id$
 */
class ilMDDescription extends ilMDBase
{
    private string $description = '';
    private ?ilMDLanguageItem $description_language = null;

    // SET/GET
    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescriptionLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->description_language = $lng_obj;
    }

    public function getDescriptionLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->description_language) ? $this->description_language : null;
    }

    public function getDescriptionLanguageCode(): string
    {
        return is_object($this->description_language) ? $this->description_language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_description_id'] = array('integer', $next_id = $this->db->nextId('il_meta_description'));

        if ($this->db->insert('il_meta_description', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_description',
            $this->__getFields(),
            array("meta_description_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_description " .
                "WHERE meta_description_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

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
            'description' => array('clob', $this->getDescription()),
            'description_language' => array('text', $this->getDescriptionLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_description " .
                "WHERE meta_description_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setDescription((string) $row->description);
                $this->setDescriptionLanguage(new ilMDLanguageItem((string) $row->description_language));
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlElement(
            'Description',
            array(
                'Language' => $this->getDescriptionLanguageCode() ?: 'en'
            ),
            $this->getDescription()
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

        $query = "SELECT meta_description_id FROM il_meta_description " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, ilDBConstants::T_INTEGER) . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, ilDBConstants::T_INTEGER) . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, ilDBConstants::T_INTEGER) . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, ilDBConstants::T_INTEGER) . " " .
            "ORDER BY meta_description_id";

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_description_id;
        }
        return $ids;
    }
}
