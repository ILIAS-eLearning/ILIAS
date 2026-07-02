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
use ILIAS\Data\Order;

abstract class EntityListing implements I\Entity
{
    use ComponentHelper;

    public function __construct(
        protected IEntity\EntityRetrieval $entity_retrieval
    ) {
    }

    /**
     * @return \Generator<IEntity\Entity>
     */
    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        ?Range $range = null,
        ?Order $order = null,
        mixed $additional_viewcontrol_data = null,
        mixed $filter_data = null,
        mixed $additional_parameters = null,
    ): \Generator {
        $range = $range ?? new Range(0, PHP_INT_MAX);
        $order = $order ?? new Order('id', Order::ASC);

        yield from $this->entity_retrieval->getEntities(
            $ui_factory,
            $range,
            $order,
            $additional_viewcontrol_data,
            $filter_data,
            $additional_parameters,
        );
    }
}
