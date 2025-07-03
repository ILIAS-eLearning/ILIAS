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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Container\Skills;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalManagerService
{
    public function getSkillManager(int $cont_obj_id, int $cont_ref_id): ContainerSkillManager
    {
        return new ContainerSkillManager($cont_obj_id, $cont_ref_id);
    }

    public function getSkillDeletionManager(): ContainerSkillDeletionManager
    {
        return new ContainerSkillDeletionManager();
    }

    public function contProfileRetrieval(
        \ILIAS\Skill\Service\SkillProfileService $profile_service,
        \ilSkillManagementSettings $skmg_settings,
        int $cont_member_role_id
    ): ContProfileRetrieval {
        return new ContProfileRetrieval(
            $profile_service,
            $skmg_settings,
            $cont_member_role_id
        );
    }

    public function contSkillRetrieval(
        ContainerSkillManager $cont_skill_manager
    ): ContSkillRetrieval {
        return new ContSkillRetrieval(
            $cont_skill_manager
        );
    }

    public function contSkillMemberRetrieval(
        ContainerSkillManager $cont_skill_manager,
        \ilContainer $container
    ): ContSkillMemberRetrieval {
        return new ContSkillMemberRetrieval(
            $cont_skill_manager,
            $container
        );
    }
}
