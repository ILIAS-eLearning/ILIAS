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
 * Export configuration for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillExportConfig extends ilExportConfig
{
    public const MODE_SKILLS = "";
    public const MODE_PROFILES = "prof";

    /**
     * @var int[]
     */
    protected array $selected_nodes = [];

    /**
     * @var int[]
     */
    protected array $selected_profiles = [];
    protected string $mode = "";
    protected int $skill_tree_id = 0;

    public function setMode(string $a_val) : void
    {
        $this->mode = $a_val;
    }

    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * @param int[] $a_val
     */
    public function setSelectedNodes(array $a_val) : void
    {
        $this->selected_nodes = $a_val;
    }

    /**
     * @return int[]
     */
    public function getSelectedNodes() : array
    {
        return $this->selected_nodes;
    }

    /**
     * @param int[] $a_val (profile ids)
     */
    public function setSelectedProfiles(array $a_val) : void
    {
        $this->selected_profiles = $a_val;
    }

    /**
     * @return int[] (profile ids)
     */
    public function getSelectedProfiles() : array
    {
        return $this->selected_profiles;
    }

    public function setSkillTreeId(int $skill_tree_id) : void
    {
        $this->skill_tree_id = $skill_tree_id;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }
}
