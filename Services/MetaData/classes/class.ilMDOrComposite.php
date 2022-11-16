<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
                $this->or_composite_id = (int) $row->orc;
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
