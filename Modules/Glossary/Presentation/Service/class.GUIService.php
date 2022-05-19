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

namespace ILIAS\Glossary\Presentation;

use ILIAS\Glossary\InternalGUIService;
use ILIAS\Glossary\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
    }

    public function request(?array $query_params = null) : PresentationGUIRequest
    {
        return new PresentationGUIRequest(
            $this->gui_service->http(),
            $this->domain_service->refinery(),
            $query_params
        );
    }

    public function ObjGlossaryGUI(int $ref_id) : \ilObjGlossaryGUI
    {
        return new \ilObjGlossaryGUI([], $ref_id, true, false);
    }
}
