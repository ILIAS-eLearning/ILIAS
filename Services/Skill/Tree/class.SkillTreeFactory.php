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

namespace ILIAS\Skill\Tree;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeFactory
{
    public function getGlobalTree(): \ilGlobalSkillTree
    {
        return new \ilGlobalSkillTree();
    }

    public function getTreeById(int $id): \ilSkillTree
    {
        return new \ilSkillTree($id);
    }

    public function getGlobalVirtualTree(): \ilGlobalVirtualSkillTree
    {
        return new \ilGlobalVirtualSkillTree();
    }

    public function getVirtualTreeById(int $id): \ilVirtualSkillTree
    {
        return new \ilVirtualSkillTree($id);
    }
}
