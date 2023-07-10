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
 *********************************************************************/

namespace ILIAS\Portfolio;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    /*public function administration() : Administration\GUIService
    {
        return new Administration\GUIService(
            $this->domain_service,
            $this
        );
    }*/

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }
}
