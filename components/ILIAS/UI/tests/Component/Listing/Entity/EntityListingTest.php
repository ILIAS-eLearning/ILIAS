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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component as I;
use ILIAS\UI\Implementation\Component\Entity;
use ILIAS\UI\Implementation\Component\Listing;

class EntityListingTest extends ILIAS_UI_TestBase
{
    public function getEntityRetrieval(): I\Entity\EntityRetrieval
    {
        return new class () implements I\Entity\EntityRetrieval {
            public function getEntities(
                ILIAS\UI\Factory $ui_factory,
                Range $range,
                Order $order,
                mixed $additional_viewcontrol_data,
                mixed $filter_data,
                mixed $additional_parameters,
            ): Generator {
                for ($i = 1; $i <= 3; $i++) {
                    yield $ui_factory->entity()->standard($i, 'primary ' . $i, 'secondary ' . $i);
                }
            }

            public function getEntitiesByIds(
                ILIAS\UI\Factory $ui_factory,
                Order $order,
                array $entity_ids,
            ): Generator {
                foreach ($entity_ids as $entity_id) {
                    yield $ui_factory->entity()->standard($entity_id, 'primary ' . $entity_id, 'secondary ' . $entity_id);
                }
            }
        };
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function listing(): Listing\Factory
            {
                return new Listing\Factory(
                    new Listing\Workflow\Factory(),
                    new Listing\CharacteristicValue\Factory(),
                    new Listing\Entity\Factory(),
                );
            }

            public function entity(): Entity\Factory
            {
                return new Entity\Factory();
            }
        };
    }

    public function testEntityListingFactory(): void
    {
        $this->assertInstanceOf(
            I\Listing\Entity\Entity::class,
            $this->getUIFactory()->listing()->entity()->standard($this->getEntityRetrieval())
        );
    }

    public function testEntityListingYieldingEntities(): void
    {
        $listing = $this->getUIFactory()->listing()->entity()
            ->standard($this->getEntityRetrieval());

        $entities = iterator_to_array($listing->getEntities($this->getUIFactory()));

        $this->assertCount(3, $entities);
        $this->assertInstanceOf(I\Entity\Entity::class, array_pop($entities));
    }
}
