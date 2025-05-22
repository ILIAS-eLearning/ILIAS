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

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component\Listing as L;

class Factory implements L\Factory
{
    public function __construct(
        protected Workflow\Factory $workflow_factory,
        protected CharacteristicValue\Factory $characteristiv_value_factory,
        protected Entity\Factory $entity_factory,
    ) {
    }

    public function unordered(array $items): Unordered
    {
        return new Unordered($items);
    }

    public function ordered(array $items): Ordered
    {
        return new Ordered($items);
    }

    public function descriptive(array $items): Descriptive
    {
        return new Descriptive($items);
    }

    public function workflow(): Workflow\Factory
    {
        return $this->workflow_factory;
    }

    public function characteristicValue(): CharacteristicValue\Factory
    {
        return $this->characteristiv_value_factory;
    }

    public function entity(): Entity\Factory
    {
        return $this->entity_factory;
    }

    public function property(): Property
    {
        return new Property();
    }
}
