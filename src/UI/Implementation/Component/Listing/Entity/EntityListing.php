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

namespace ILIAS\UI\Implementation\Component\Listing\Entity;

use ILIAS\UI\Component\Listing\Entity as I;
use ILIAS\UI\Component\Entity as IEntity;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Range;

abstract class EntityListing implements I\EntityListing
{
    use ComponentHelper;

    protected I\DataRetrieval $data;

    public function __construct(
        protected I\RecordToEntity $entity_mapping
    ) {
    }

    public function withData(I\DataRetrieval $data): self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    /**
     * @param array<string,mixed> $additional_parameters
     * @return \Generator<IEntity\Entity>
     */
    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range = null,
        array $additional_parameters = null
    ): \Generator {
        $mapping = new class ($this->entity_mapping, $ui_factory) implements I\Mapping {
            public function __construct(
                protected I\RecordToEntity $mapper,
                protected \ILIAS\UI\Factory $ui_factory
            ) {
            }

            public function map(mixed $record): IEntity\Entity
            {
                return $this->mapper->map($this->ui_factory, $record);
            }
        };

        $additional_parameters = null;
        return $this->data->getEntities($mapping, $range, $additional_parameters);
    }
}
