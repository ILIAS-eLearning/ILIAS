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

class ContainerSkillDBRepository
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

    public function add(int $cont_obj_id, int $skill_id, int $tref_id): void
    {
        $this->db->replace(
            "cont_skills",
            [
            "id" => ["integer", $cont_obj_id],
            "skill_id" => ["integer", $skill_id],
            "tref_id" => ["integer", $tref_id]
            ],
            []
        );
    }

    public function remove(int $cont_obj_id, int $skill_id, int $tref_id): void
    {
        $this->db->manipulate(
            "DELETE FROM cont_skills WHERE " .
            " id = " . $this->db->quote($cont_obj_id, "integer") .
            " AND skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer")
        );
    }

    /**
     * @return ContainerSkill[]
     */
    public function getAll(int $cont_obj_id): array
    {
        $skills = [];
        $set = $this->db->query(
            "SELECT * FROM cont_skills " .
            " WHERE id  = " . $this->db->quote($cont_obj_id, "integer")
        );

        while ($rec = $this->db->fetchAssoc($set)) {
            $skills[] = $this->getContainerSkillFromRecord($rec);
        }
        return $skills;
    }

    protected function getContainerSkillFromRecord(array $rec): ContainerSkill
    {
        $rec["id"] = (int) $rec["id"];
        $rec["skill_id"] = (int) $rec["skill_id"];
        $rec["tref_id"] = (int) $rec["tref_id"];

        return $this->factory_service->containerSkill()->skill(
            $rec["id"],
            $rec["skill_id"],
            $rec["tref_id"]
        );
    }
}
