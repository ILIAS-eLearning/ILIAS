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

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalGUIService
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected SkillContainerGUIRequest $request;

    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $query_params = null,
        ?array $post_data = null
    ) {
        global $DIC;

        $this->http = $http;
        $this->refinery = $refinery;

        $this->request = new SkillContainerGUIRequest(
            $this->http,
            $this->refinery,
            $query_params,
            $post_data
        );
    }

    /**
     * Get request wrappers. If dummy data is provided the usual http wrapper will
     * not be used.
     */

    public function request(): SkillContainerGUIRequest
    {
        return $this->request;
    }

    public function contProfileTableBuilder(
        SkillInternalManagerService $manager_service,
        \ILIAS\Skill\Service\SkillProfileService $profile_service,
        \ilSkillManagementSettings $skmg_settings,
        int $cont_ref_id,
        int $cont_member_role_id,
        object $parent_gui,
        string $parent_cmd
    ): ContProfileTableBuilder {
        return new ContProfileTableBuilder(
            $manager_service,
            $profile_service,
            $skmg_settings,
            $cont_ref_id,
            $cont_member_role_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function contSkillTableBuilder(
        SkillInternalManagerService $manager_service,
        ContainerSkillManager $cont_skill_manager,
        int $container_obj_id,
        int $container_ref_id,
        object $parent_gui,
        string $parent_cmd
    ): ContSkillTableBuilder {
        return new ContSkillTableBuilder(
            $manager_service,
            $cont_skill_manager,
            $container_obj_id,
            $container_ref_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function contSkillMemberTableBuilder(
        SkillInternalManagerService $manager_service,
        ContainerSkillManager $cont_skill_manager,
        \ilContainer $container,
        object $parent_gui,
        string $parent_cmd
    ): ContSkillMemberTableBuilder {
        return new ContSkillMemberTableBuilder(
            $manager_service,
            $cont_skill_manager,
            $container,
            $parent_gui,
            $parent_cmd
        );
    }
}
