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

namespace ILIAS\UI\Component\Entity;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

/**
 * This describes how entities should be retrieved for different purposes.
 */
interface EntityRetrieval
{
    /**
     * This method is used by the Entity Listing to visualise all available entities.
     *
     * The signature mirrors {@see \ILIAS\UI\Component\Table\DataRetrieval::getRows()},
     * so the UI framework can create expectations (order/range/grouping/etc.) globally
     * and it allows future extension of additional (view/filter) controls.
     *
     * @return Generator<Entity>
     */
    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator;

    /**
     * This method is used by the Prompt State to confirm a subset of entities.
     *
     * @param  array<int|string> $entity_ids
     * @return Generator<Entity>
     */
    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator;
}
