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

use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\InternalRepoService;
use ILIAS\Help\GuidedTour\InternalDomainService;

class StepManager
{
    protected StepDBRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
    ) {
        $this->repo = $repo->step();
    }

    public function create(Step $step): int
    {
        return $this->repo->create($step);
    }

    public function update(Step $step): void
    {
        $this->repo->update($step);
    }

    public function getById(int $id): ?Step
    {
        return $this->repo->getById($id);
    }

    public function getStepName(StepType $step_type): string
    {
        $lng = $this->domain->lng();
        return match($step_type) {
            StepType::Mainbar => $lng->txt("gdtr_mainbar"),
            StepType::Metabar => $lng->txt("gdtr_metabar"),
            StepType::Tab => $lng->txt("gdtr_tabs"),
            StepType::Form => $lng->txt("gdtr_form"),
            StepType::Table => $lng->txt("gdtr_table"),
            StepType::PrimaryButton => $lng->txt("gdtr_primary_button"),
            StepType::Toolbar => $lng->txt("gdtr_toolbar"),
        };
    }


    /**
     * @return \Generator<Step>
     */
    public function getStepsOfTour(int $tour_id): \Generator
    {
        yield from $this->repo->getStepsOfTour($tour_id);
    }

    public function countStepsOfTour(int $tour_id): int
    {
        return $this->repo->countStepsOfTour($tour_id);
    }

    public function saveOrder(int $tour_id, array $order): void
    {
        $this->repo->saveOrder($tour_id, $order);
    }

    public function delete(int $tour_id, int $step_id): void
    {
        $this->repo->delete($tour_id, $step_id);
    }

}
