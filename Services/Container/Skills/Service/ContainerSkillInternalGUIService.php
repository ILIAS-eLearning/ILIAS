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
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerSkillInternalGUIService
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected SkillContainerGUIRequest $request;

    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        array $query_params = null,
        array $post_data = null
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
}
