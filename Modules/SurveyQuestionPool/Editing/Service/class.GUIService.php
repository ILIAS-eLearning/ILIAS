<?php declare(strict_types = 1);

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

namespace ILIAS\SurveyQuestionPool\Editing;

use ILIAS\SurveyQuestionPool\InternalGUIService;
use ILIAS\SurveyQuestionPool\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $ui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalGUIService $ui_service,
        InternalDomainService $domain_service
    ) {
        $this->ui_service = $ui_service;
        $this->domain_service = $domain_service;
    }

    public function request() : EditingGUIRequest
    {
        return new EditingGUIRequest(
            $this->ui_service->http(),
            $this->domain_service->refinery()
        );
    }
}
