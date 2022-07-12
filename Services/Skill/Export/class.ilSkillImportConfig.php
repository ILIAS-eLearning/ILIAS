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
 * Import configuration for skills
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillImportConfig extends ilImportConfig
{
    protected int $skill_tree_id = 0;

    public function setSkillTreeId(int $skill_tree_id) : void
    {
        $this->skill_tree_id = $skill_tree_id;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }
}
