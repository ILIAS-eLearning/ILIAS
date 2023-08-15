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
 ********************************************************************
 */

namespace ILIAS\Container\Skills;

class ContainerMemberSkillDBRepository
{
    protected \ilDBInterface $db;
    protected ContainerSkillInternalFactoryService $factory_service;

    public function __construct(
        \ilDBInterface $db = null,
        ContainerSkillInternalFactoryService $factory_service = null,
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internalContainer()->factory();
    }

    public function add(int $cont_obj_id, int $user_id, int $skill_id, int $tref_id, int $level_id): void
    {
        $this->db->replace(
            "cont_member_skills",
            [
                "obj_id" => ["integer", $cont_obj_id],
                "user_id" => ["integer", $user_id],
                "skill_id" => ["integer", $skill_id],
                "tref_id" => ["integer", $tref_id]
            ],
            [
                "level_id" => ["integer", $level_id],
                "published" => ["integer", 0]
            ]
        );
    }

    public function remove(int $cont_obj_id, int $user_id, int $skill_id, int $tref_id): void
    {
        $this->db->manipulate("DELETE FROM cont_member_skills " .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer") .
            " AND skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer"));
    }

    public function removeAll(int $cont_obj_id, int $user_id): void
    {
        $this->db->manipulate("DELETE FROM cont_member_skills " .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer"));
    }

    public function publish(int $cont_obj_id, int $user_id): void
    {
        $this->db->manipulate("UPDATE cont_member_skills SET " .
            " published = " . $this->db->quote(1, "integer") .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer"));
    }

    public function getPublished(int $cont_obj_id, int $user_id): bool
    {
        $set = $this->db->query(
            "SELECT published FROM cont_member_skills " .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            if ((bool) $rec["published"] === true) { // this is a little weak, but the value should be the same for all save skills
                return true;
            }
        }
        return false;
    }

    /**
     * @return ContainerMemberSkill[]
     */
    public function getAll(int $cont_obj_id, int $user_id): array
    {
        $mem_skills = [];
        $set = $this->db->query(
            "SELECT * FROM cont_member_skills " .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $mem_skills[] = $this->getContainerMemberSkillFromRecord($rec);
        }
        return $mem_skills;
    }

    public function getLevel(int $cont_obj_id, int $user_id, int $skill_id, int $tref_id): ?int
    {
        $set = $this->db->query(
            "SELECT * FROM cont_member_skills " .
            " WHERE obj_id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer") .
            " AND skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer")
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["level_id"];
        }
        return null;
    }

    protected function getContainerMemberSkillFromRecord(array $rec): ContainerMemberSkill
    {
        $rec["obj_id"] = (int) $rec["obj_id"];
        $rec["user_id"] = (int) $rec["user_id"];
        $rec["skill_id"] = (int) $rec["skill_id"];
        $rec["tref_id"] = (int) $rec["tref_id"];
        $rec["level_id"] = (int) $rec["level_id"];
        $rec["published"] = (bool) $rec["published"];

        return $this->factory_service->containerSkill()->memberSkill(
            $rec["obj_id"],
            $rec["user_id"],
            $rec["skill_id"],
            $rec["tref_id"],
            $rec["level_id"],
            $rec["published"]
        );
    }
}
