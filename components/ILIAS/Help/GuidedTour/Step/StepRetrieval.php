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

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Help\GuidedTour\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;

class StepRetrieval implements RetrievalInterface
{
    protected \ILIAS\Help\GuidedTour\Step\StepManager $step_manager;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $tour_id
    ) {
        $this->step_manager = $domain->step();
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->step_manager->getStepsOfTour($this->tour_id) as $step) {
            yield [
                "id" => $step->getId(),
                "type" => $this->step_manager->getStepName($step->getType()),
                "element_id" => $step->getElementId()
            ];
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return $this->step_manager->countStepsOfTour($this->tour_id);
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }
}
