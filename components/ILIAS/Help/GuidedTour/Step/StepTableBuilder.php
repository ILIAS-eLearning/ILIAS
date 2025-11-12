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

namespace ILIAS\Help\GuidedTour\Step;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Help\GuidedTour\InternalDomainService;
use ILIAS\Help\GuidedTour\InternalGUIService;

class StepTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $tour_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "gdtr_steps";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("gdtr_tour_steps");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        //$request = $this->gui->editing()->request();
        return $this->domain->stepRetrieval(
            $this->tour_id,
        );
    }

    protected function getOrderingCommand(): string
    {
        return "saveOrder";
    }

    protected function transformRow(array $data_row): array
    {
        return $data_row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $table = $table
            ->textColumn("type", $lng->txt("gdtr_type"))
            ->textColumn("element_id", $lng->txt("gdtr_element_id"))
            ->singleAction("editStep", $lng->txt("gdtr_edit_properties"))
            ->singleAction("editPage", $lng->txt("gdtr_edit_content"))
            ->singleAction("confirmStepDeletion", $lng->txt("delete"), true);
        return $table;
    }

}
