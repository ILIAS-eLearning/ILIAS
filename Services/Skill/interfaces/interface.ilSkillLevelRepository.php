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
 * Interface ilSkillLevelRepository
 */
interface ilSkillLevelRepository
{
    public function deleteLevelsOfSkill(int $skill_id) : void;

    public function addLevel(int $skill_id, string $a_title, string $a_description, string $a_import_id = "") : void;

    public function getLevelData(int $skill_id, int $a_id = 0) : array;

    public function lookupLevelTitle(int $a_id) : string;

    public function lookupLevelDescription(int $a_id) : string;

    public function lookupLevelSkillId(int $a_id) : int;

    public function writeLevelTitle(int $a_id, string $a_title) : void;

    public function writeLevelDescription(int $a_id, string $a_description) : void;

    public function updateLevelOrder(array $order) : void;

    public function deleteLevel(int $a_id) : void;

    public function fixLevelNumbering(int $skill_id) : void;

    public function getSkillForLevelId(int $a_level_id) : ?ilBasicSkill;
}
