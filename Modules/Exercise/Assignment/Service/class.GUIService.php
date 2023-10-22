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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;

class GUIService
{
    protected InternalDomainService $domain_service;
    protected InternalGUIService $gui_service;


    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
    }

    public function itemBuilder(
        \ilObjExercise $exc,
        MandatoryAssignmentsManager $mandatory_manager
    ): ItemBuilderUI {
        return new ItemBuilderUI(
            $this->propertyAndActionBuilder($exc, $mandatory_manager),
            $this->gui_service->ui()->factory(),
            $this->gui_service->ctrl()
        );
    }

    public function panelBuilder(
        \ilObjExercise $exc,
        MandatoryAssignmentsManager $mandatory_manager
    ): PanelBuilderUI {
        return new PanelBuilderUI(
            $this->propertyAndActionBuilder($exc, $mandatory_manager),
            $this->gui_service->ui()->factory(),
            $this->gui_service->ui()->renderer(),
            $this->gui_service->ctrl(),
            $this->domain_service->lng()
        );
    }

    public function propertyAndActionBuilder(
        \ilObjExercise $exc,
        MandatoryAssignmentsManager $mandatory_manager
    ): PropertyAndActionBuilderUI {
        return new PropertyAndActionBuilderUI(
            $exc,
            $mandatory_manager,
            $this->domain_service,
            $this->gui_service
        );
    }

    public function assignmentPresentationGUI(\ilObjExercise $exc): \ilAssignmentPresentationGUI
    {
        return new \ilAssignmentPresentationGUI(
            $exc,
            $this->domain_service,
            $this->gui_service
        );
    }

    public function types(): \ilExAssignmentTypesGUI
    {
        return new \ilExAssignmentTypesGUI();
    }
}
