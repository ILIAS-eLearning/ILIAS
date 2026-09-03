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

namespace ILIAS\Help\GuidedTour;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Export\PrintProcessGUI;
use ilGuidedTourGUI;
use ilGuidedTourAdminGUI;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Help\GuidedTour\Step\StepTableBuilder;

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

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function guidedTourGUI(): ilGuidedTourGUI
    {
        return new ilGuidedTourGUI();
    }

    public function objectGUI(int $obj_id): \ilObjGuidedTourGUI
    {
        return new \ilObjGuidedTourGUI([], $obj_id, false);
    }

    public function adminGUI(bool $edit = false): ilGuidedTourAdminGUI
    {
        return new ilGuidedTourAdminGUI(
            $this->data_service,
            $this->domain_service,
            $this,
            $edit
        );
    }

    public function stepTableBuilder(
        int $tour_id,
        object $parent_gui,
        string $parent_cmd
    ): StepTableBuilder {
        return new StepTableBuilder(
            $this->domain_service,
            $this,
            $tour_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function stepTableGUI(
        int $tour_id,
        object $parent_gui,
        string $parent_cmd
    ): TableAdapterGUI {
        return $this->stepTableBuilder(
            $tour_id,
            $parent_gui,
            $parent_cmd
        )->getTable();
    }
}
