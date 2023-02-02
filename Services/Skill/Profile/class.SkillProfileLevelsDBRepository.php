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

namespace ILIAS\Skill\Profile;

use ILIAS\Skill\Service;

class SkillProfileLevelsDBRepository
{
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    /**
     * @return SkillProfileLevel[]
     */
    public function get(int $profile_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_level " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );

        $levels = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $levels[] = $this->getFromRecord($rec);
        }

        return $levels;
    }

    protected function getFromRecord(array $rec): SkillProfileLevel
    {
        $rec["profile_id"] = (int) $rec["profile_id"];
        $rec["base_skill_id"] = (int) $rec["base_skill_id"];
        $rec["tref_id"] = (int) $rec["tref_id"];
        $rec["level_id"] = (int) $rec["level_id"];
        $rec["order_nr"] = (int) $rec["order_nr"];

        return $this->factory_service->profile()->profileLevel(
            $rec["profile_id"],
            $rec["base_skill_id"],
            $rec["tref_id"],
            $rec["level_id"],
            $rec["order_nr"]
        );
    }

    public function create(SkillProfileLevel $skill_level_obj): void
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "skl_profile_level",
            array("profile_id" => array("integer", $skill_level_obj->getProfileId()),
                  "tref_id" => array("integer", $skill_level_obj->getTrefId()),
                  "base_skill_id" => array("integer", $skill_level_obj->getBaseSkillId())
            ),
            array("order_nr" => array("integer", $skill_level_obj->getOrderNr()),
                  "level_id" => array("integer", $skill_level_obj->getLevelId())
            )
        );
    }

    public function delete(SkillProfileLevel $skill_level_obj): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " base_skill_id = " . $ilDB->quote($skill_level_obj->getBaseSkillId(), "integer") .
            " AND tref_id = " . $ilDB->quote($skill_level_obj->getTrefId(), "integer") .
            " AND level_id = " . $ilDB->quote($skill_level_obj->getLevelId(), "integer") .
            " AND order_nr = " . $ilDB->quote($skill_level_obj->getOrderNr(), "integer")
        );
    }

    public function deleteAll(int $profile_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    public function updateSkillOrder(int $profile_id, array $order): void
    {
        $ilDB = $this->db;

        $cnt = 1;
        foreach ($order as $id => $o) {
            $id_arr = explode("_", $id);
            $ilDB->manipulate(
                "UPDATE skl_profile_level SET " .
                " order_nr = " . $ilDB->quote(($cnt * 10), "integer") .
                " WHERE base_skill_id = " . $ilDB->quote($id_arr[0], "integer") .
                " AND tref_id = " . $ilDB->quote($id_arr[1], "integer") .
                " AND profile_id = " . $ilDB->quote($profile_id, "integer")
            );
            $cnt++;
        }
    }

    public function fixSkillOrderNumbering(int $profile_id): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT profile_id, base_skill_id, tref_id, order_nr FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer") .
            " ORDER BY order_nr ASC"
        );
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE skl_profile_level SET " .
                " order_nr = " . $ilDB->quote(($cnt * 10), "integer") .
                " WHERE profile_id = " . $ilDB->quote($rec["profile_id"], "integer") .
                " AND base_skill_id = " . $ilDB->quote($rec["base_skill_id"], "integer") .
                " AND tref_id = " . $ilDB->quote($rec["tref_id"], "integer")
            );
            $cnt++;
        }
    }

    public function getMaxOrderNr(int $profile_id): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT MAX(order_nr) mnr FROM skl_profile_level WHERE " .
            " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["mnr"];
    }
}
