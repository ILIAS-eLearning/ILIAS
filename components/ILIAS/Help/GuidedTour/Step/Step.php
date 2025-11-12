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

class Step
{
    public function __construct(
        protected int $id,
        protected int $tour_id,
        protected int $order_nr,
        protected StepType $type,
        protected string $element_id
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTourId(): int
    {
        return $this->tour_id;
    }

    public function getOrderNr(): int
    {
        return $this->order_nr;
    }

    public function getType(): StepType
    {
        return $this->type;
    }

    public function getElementId(): string
    {
        return $this->element_id;
    }
}
