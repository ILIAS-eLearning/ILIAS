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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerSkillDeletionManager
{
    protected ContainerSkillDBRepository $cont_skill_repo;
    protected ContainerMemberSkillDBRepository $cont_member_skill_repo;

    public function __construct(
        ContainerSkillDBRepository $cont_skill_repo = null,
        ContainerMemberSkillDBRepository $cont_member_skill_repo = null
    ) {
        global $DIC;

        $this->cont_skill_repo = ($cont_skill_repo)
            ?: $DIC->skills()->internalContainer()->repo()->getContainerSkillRepo();
        $this->cont_member_skill_repo = ($cont_member_skill_repo)
            ?: $DIC->skills()->internalContainer()->repo()->getContainerMemberSkillRepo();
    }

    public function removeContainerSkillsForSkill(int $skill_node_id, bool $is_reference = false): void
    {
        $this->cont_skill_repo->removeForSkill($skill_node_id, $is_reference);
    }

    public function removeContainerMemberSkillsForSkill(int $skill_node_id, bool $is_reference = false): void
    {
        $this->cont_member_skill_repo->removeForSkill($skill_node_id, $is_reference);
    }
}
