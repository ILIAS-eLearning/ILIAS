<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import configuration for skills
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillImportConfig extends ilImportConfig
{
    protected $skill_tree_id = 0;

    public function setSkillTreeId(int $skill_tree_id)
    {
        $this->skill_tree_id = $skill_tree_id;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }
}
