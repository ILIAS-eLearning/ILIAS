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

namespace ILIAS\UI\Implementation\Component\Prompt\State;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * Wraps an EntityRetrieval and limits getEntities to a fixed set of IDs.
 */
class SubsetEntityRetrieval implements EntityRetrieval
{
    /**
     * @param array<int|string> $entity_ids
     */
    public function __construct(
        private readonly EntityRetrieval $entity_retrieval,
        private readonly array $entity_ids,
    ) {
    }

    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        yield from $this->entity_retrieval->getEntitiesByIds($ui_factory, $order, $this->entity_ids);
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        yield from $this->entity_retrieval->getEntitiesByIds($ui_factory, $order, $entity_ids);
    }
}
