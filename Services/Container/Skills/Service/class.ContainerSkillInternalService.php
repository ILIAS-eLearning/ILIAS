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

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * @author famula@leifos.de
 */
class ContainerSkillInternalService
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function repo(): ContainerSkillInternalRepoService
    {
        return new ContainerSkillInternalRepoService();
    }

    public function manager(): ContainerSkillInternalManagerService
    {
        return new ContainerSkillInternalManagerService();
    }

    /**
     * Skill service repos
     */
    public function factory(): ContainerSkillInternalFactoryService
    {
        return new ContainerSkillInternalFactoryService();
    }

    public function gui(
        array $query_params = null,
        array $post_data = null
    ): ContainerSkillInternalGUIService {
        return new ContainerSkillInternalGUIService(
            $this->http,
            $this->refinery,
            $query_params = null,
            $post_data = null
        );
    }
}
