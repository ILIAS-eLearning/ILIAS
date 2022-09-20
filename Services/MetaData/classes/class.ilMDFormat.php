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
 * Meta Data class (element format)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-core
 * @version $Id$
 */
class ilMDFormat extends ilMDBase
{
    private string $format = '';

    // SET/_GET
    public function setFormat(string $a_format): void
    {
        $this->format = $a_format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_format_id'] = array('integer', $next_id = $this->db->nextId('il_meta_format'));

        if ($this->db->insert('il_meta_format', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_format',
            $this->__getFields(),
            array("meta_format_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_format " .
                "WHERE meta_format_id = " . $this->db->quote($this->getMetaId(), 'integer');
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
            'format' => array('text', $this->getFormat())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_format " .
                "WHERE meta_format_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setFormat($row->format);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        if ($this->getFormat()) {
            $writer->xmlElement('Format', null, $this->getFormat());
        }
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_format_id FROM il_meta_format " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_format_id;
        }
        return $ids;
    }
}
