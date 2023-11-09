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
class SkillInternalService
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function repo(): SkillInternalRepoService
    {
        return new SkillInternalRepoService();
    }

    public function manager(): SkillInternalManagerService
    {
        return new SkillInternalManagerService();
    }

    /**
     * Skill service repos
     */
    public function factory(): SkillInternalFactoryService
    {
        return new SkillInternalFactoryService();
    }

    public function gui(
        array $query_params = null,
        array $post_data = null
    ): SkillInternalGUIService {
        return new SkillInternalGUIService(
            $this->http,
            $this->refinery,
            $query_params = null,
            $post_data = null
        );
    }
}
