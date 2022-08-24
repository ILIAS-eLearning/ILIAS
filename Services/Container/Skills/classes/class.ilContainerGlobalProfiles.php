<?php

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
 ********************************************************************
 */

/**
 * Global competence profiles of a container
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilContainerGlobalProfiles
{
    protected ilDBInterface $db;
    protected array $profiles = [];
    protected int $obj_id = 0;
    protected int $mem_rol_id = 0;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setObjId($a_obj_id);

        if ($this->getObjId() > 0) {
            $this->setMemberRoleId();
            $this->read();
        }
    }

    protected function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    protected function getObjId(): int
    {
        return $this->obj_id;
    }

    protected function setMemberRoleId(): void
    {
        $refs = ilObject::_getAllReferences($this->getObjId());
        $ref_id = end($refs);
        $this->mem_rol_id = ilParticipants::getDefaultMemberRole($ref_id);
    }

    protected function getMemberRoleId(): int
    {
        return $this->mem_rol_id;
    }

    public function resetProfiles(): void
    {
        $this->profiles = [];
    }

    public function addProfile(int $a_profile_id): void
    {
        $this->profiles[$a_profile_id] = [
            "profile_id" => $a_profile_id
        ];
    }

    public function removeProfile(int $a_profile_id): void
    {
        unset($this->profiles[$a_profile_id]);
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    protected function read(): void
    {
        $db = $this->db;

        $this->profiles = [];
        $set = $db->query(
            "SELECT spr.profile_id, spr.role_id, sp.title FROM skl_profile_role spr INNER JOIN skl_profile sp " .
            " ON spr.profile_id = sp.id " .
            " WHERE sp.ref_id = 0 " .
            " AND role_id  = " . $db->quote($this->getMemberRoleId(), "integer")
        );
        while ($rec = $db->fetchAssoc($set)) {
            $this->profiles[$rec["profile_id"]] = $rec;
        }
    }

    protected function delete(): void
    {
        $db = $this->db;

        $db->manipulate(
            "DELETE spr FROM skl_profile_role spr INNER JOIN skl_profile sp " .
            " ON spr.profile_id = sp.id " .
            " WHERE sp.ref_id = 0 " .
            " AND role_id = " . $db->quote($this->getMemberRoleId(), "integer")
        );
    }

    public function save(): void
    {
        $db = $this->db;

        $this->delete();
        foreach ($this->profiles as $p) {
            $db->manipulate("INSERT INTO skl_profile_role " .
                "(role_id, profile_id) VALUES (" .
                $db->quote($this->getMemberRoleId(), "integer") . "," .
                $db->quote($p["profile_id"], "integer") . ")");
        }
    }
}
