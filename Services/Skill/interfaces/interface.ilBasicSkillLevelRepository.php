<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBasicSkillLevelRepository
 */
interface ilBasicSkillLevelRepository
{

    /**
     * Delete levels of a skill
     * @param int $skill_id
     */
    public function deleteLevelsOfSkill(int $skill_id);

    /**
     * Add new level
     * @param int    $skill_id
     * @param string $a_title
     * @param string $a_description
     * @param string $a_import_id
     */
    public function addLevel(int $skill_id, string $a_title, string $a_description, string $a_import_id = "");

    /**
     * Get level data
     * @param int $skill_id
     * @param int $a_id
     * @return array level data
     */
    public function getLevelData(int $skill_id, int $a_id = 0) : array;

    /**
     * Lookup level title
     * @param int $a_id level id
     * @return string level title
     */
    public function lookupLevelTitle(int $a_id) : string;

    /**
     * Lookup level description
     * @param int $a_id level id
     * @return string level description
     */
    public function lookupLevelDescription(int $a_id) : string;

    /**
     * Lookup level skill id
     * @param int $a_id level id
     * @return int skill id
     */
    public function lookupLevelSkillId(int $a_id) : int;

    /**
     * Write level title
     * @param int    $a_id    level id
     * @param string $a_title level title
     */
    public function writeLevelTitle(int $a_id, string $a_title);

    /**
     * Write level description
     * @param int    $a_id          level id
     * @param string $a_description level description
     */
    public function writeLevelDescription(int $a_id, string $a_description);

    /**
     * Update level order
     * @param array $order
     */
    public function updateLevelOrder(array $order);

    /**
     * Delete level
     * @param int $a_id
     */
    public function deleteLevel(int $a_id);

    /**
     * Fix level numbering
     * @param int $skill_id
     */
    public function fixLevelNumbering(int $skill_id);

    /**
     * Get skill for level id
     * @param int $a_level_id
     * @return null|ilBasicSkill
     */
    public function getSkillForLevelId(int $a_level_id);

}