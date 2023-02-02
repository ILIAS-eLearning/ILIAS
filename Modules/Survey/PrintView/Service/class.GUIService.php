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

namespace ILIAS\Survey\PrintView;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Export\PrintViewProvider;
use ILIAS\Export\PrintProcessGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $ui_service;
    protected \ilObjectServiceInterface $object_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalGUIService $ui_service,
        \ilObjectServiceInterface $object_service,
        InternalDomainService $domain_service
    ) {
        $this->ui_service = $ui_service;
        $this->object_service = $object_service;
        $this->domain_service = $domain_service;
    }

    public function page(int $ref_id): PrintProcessGUI
    {
        $provider = new PagePrintViewProviderGUI(
            $this->domain_service->lng(),
            $this->ui_service->ctrl(),
            $ref_id
        );
        return $this->getPrintProcessGUI($provider);
    }

    public function list(int $ref_id): PrintProcessGUI
    {
        $provider = new ListPrintViewProviderGUI(
            $this->domain_service->lng(),
            $this->ui_service->ctrl(),
            $ref_id
        );
        return $this->getPrintProcessGUI($provider);
    }


    public function resultsOverview(int $ref_id): PrintProcessGUI
    {
        $provider = new ResultsOverviewPrintViewProviderGUI(
            $this->domain_service->lng(),
            $this->ui_service->ctrl(),
            $ref_id
        );
        return $this->getPrintProcessGUI($provider);
    }

    public function resultsDetails(int $ref_id): PrintProcessGUI
    {
        $provider = new ResultsDetailsPrintViewProviderGUI(
            $this->domain_service->lng(),
            $this->ui_service->ctrl(),
            $ref_id
        );
        return $this->getPrintProcessGUI($provider);
    }

    public function resultsPerUser(int $ref_id): PrintProcessGUI
    {
        $provider = new ResultsPerUserPrintViewProviderGUI(
            $this->domain_service->lng(),
            $this->ui_service->ctrl(),
            $ref_id
        );
        return $this->getPrintProcessGUI($provider);
    }

    protected function getPrintProcessGUI(PrintViewProvider $provider): PrintProcessGUI
    {
        return new PrintProcessGUI(
            $provider,
            $this->ui_service->http(),
            $this->ui_service->ui(),
            $this->domain_service->lng()
        );
    }
}
