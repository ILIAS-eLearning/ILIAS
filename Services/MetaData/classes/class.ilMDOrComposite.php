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
 * Meta Data class (element orComposite)
 * Extends MDRequirement
 * @package ilias-core
 * @version $Id$
 */
class ilMDOrComposite extends ilMDRequirement
{
    private int $or_composite_id = 0;

    // SET/GET
    public function setOrCompositeId(int $a_or_composite_id): void
    {
        $this->or_composite_id = $a_or_composite_id;
    }

    public function getOrCompositeId(): int
    {
        if (!$this->or_composite_id) {
            $query = "SELECT MAX(or_composite_id) orc FROM il_meta_requirement " .
                "WHERE rbac_id = " . $this->db->quote($this->getRBACId(), 'integer') . " " .
                "AND obj_id = " . $this->db->quote($this->getObjId(), 'integer') . " ";

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->or_composite_id = $row->orc;
            }
            ++$this->or_composite_id;
        }
        return $this->or_composite_id;
    }

    /**
     * @return int[]
     */
    public function getRequirementIds(): array
    {
        return ilMDRequirement::_getIds(
            $this->getRBACId(),
            $this->getObjId(),
            $this->getParentId(),
            'meta_technical',
            $this->getOrCompositeId()
        );
    }

    public function getRequirement(int $a_requirement_id): ?ilMDRequirement
    {
        if (!$a_requirement_id) {
            return null;
        }
        $req = new ilMDRequirement();
        $req->setMetaId($a_requirement_id);

        return $req;
    }

    public function addRequirement(): ilMDRequirement
    {
        $req = new ilMDRequirement($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $req->setParentId($this->getParentId());
        $req->setParentType('meta_technical');
        $req->setOrCompositeId($this->getOrCompositeId());

        return $req;
    }

    public function save(): int
    {
        echo 'Use ilMDOrcomposite::addRequirement()';
        return 0;
    }

    public function delete(): bool
    {
        foreach ($this->getRequirementIds() as $id) {
            $req = $this->getRequirement($id);
            $req->delete();
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        // For all requirements
        $writer->xmlStartTag('OrComposite');

        $reqs = $this->getRequirementIds();
        foreach ($reqs as $id) {
            $req = $this->getRequirement($id);
            $req->toXML($writer);
        }
        if (!count($reqs)) {
            $req = new ilMDRequirement($this->getRBACId(), $this->getObjId());
            $req->toXML($writer);
        }
        $writer->xmlEndTag('OrComposite');
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(
        int $a_rbac_id,
        int $a_obj_id,
        int $a_parent_id,
        string $a_parent_type,
        int $a_or_composite_id = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT(or_composite_id) or_composite_id FROM il_meta_requirement " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text') . " " .
            "AND or_composite_id > 0 ";

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->or_composite_id;
        }
        return $ids;
    }
}
