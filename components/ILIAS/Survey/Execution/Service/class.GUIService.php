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

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;

class GUIService
{
    protected static array $instance = [];

    public function __construct(
        protected InternalGUIService $ui_service,
        protected InternalDomainService $domain_service
    ) {
    }

    public function request(): ExecutionGUIRequest
    {
        return self::$instance["ex_request"] ??
            self::$instance["ex_request"] = new ExecutionGUIRequest(
                $this->ui_service->http(),
                $this->domain_service->refinery()
            );
    }

    public function launchGUI(
        \ilObjSurvey $survey
    ): LaunchGUI {
        return self::$instance["launch_gui"][$survey->getId()] ??
            self::$instance["launch_gui"][$survey->getId()] = new LaunchGUI(
                $this->domain_service,
                $this->ui_service,
                $survey
            );
    }
}
