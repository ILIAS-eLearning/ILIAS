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

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use Generator;

abstract class Entity implements C\Listing\Entity\Entity
{
    use ComponentHelper;

    /** @var array<string|int>|null */
    protected ?array $subset = null;

    public function __construct(
        protected C\Entity\EntityRetrieval $entity_retrieval
    ) {
    }

    public function withSubset(array $entity_ids): static
    {
        $this->checkArgListElements("entity_ids", $entity_ids, ["string", "int"]);
        $clone = clone $this;
        $clone->subset = $entity_ids;
        return $clone;
    }

    /**
     * Yields Entities created by the given {@see C\Entity\EntityRetrieval},
     * respecting {@see C\Listing\Entity\Entity::withSubset()}.
     *
     * This is not implemented inside the renderer so we can add support for
     * View Control's more easily at a later time.
     *
     * @return \Generator<C\Entity\Entity>
     */
    public function getEntities(\ILIAS\UI\Factory $ui_factory): Generator
    {
        if (null !== $this->subset) {
            yield from $this->entity_retrieval->getEntitiesByIds($ui_factory, $this->getOrder(), $this->subset);
            return;
        }

        yield from $this->entity_retrieval->getEntities(
            $ui_factory,
            $this->getRange(),
            $this->getOrder(),
            null,
            null,
            null,
        );
    }

    protected function getOrder(): Order
    {
        return new Order('', Order::ASC);
    }

    protected function getRange(): Range
    {
        return new Range(0, PHP_INT_MAX);
    }
}
