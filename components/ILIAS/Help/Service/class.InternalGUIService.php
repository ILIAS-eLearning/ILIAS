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

namespace ILIAS\Help;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Export\PrintProcessGUI;
use ilGuidedTourGUI;

class InternalGUIService
{
    use GlobalDICGUIServices;

    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalDataService $data_service,
        protected InternalDomainService $domain_service
    ) {
        $this->initGUIServices($DIC);
    }

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function guidedTour(): \ILIAS\Help\GuidedTour\InternalGUIService
    {
        return self::$instance["guided_gui"] ??= new GuidedTour\InternalGUIService(
            $this->DIC,
            $this->data_service->guidedTour(),
            $this->domain_service->guidedTour()
        );
    }
}
