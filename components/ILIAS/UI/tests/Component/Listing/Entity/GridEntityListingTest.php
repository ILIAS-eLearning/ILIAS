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

use ILIAS\UI\Implementation\Component\Entity;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Range;

class GridEntityListingTest extends ILIAS_UI_TestBase
{
    public function getEntityMapping(): C\Listing\Entity\RecordToEntity
    {
        return new class () implements C\Listing\Entity\RecordToEntity {
            public function map(
                UIFactory $ui_factory,
                mixed $record
            ): Entity\Entity {
                return $ui_factory->entity()->standard('primary', 'secondary');
            }
        };
    }
    public function getUIFactory(): NoUIFactory
    {
        return new class (
            $this->createMock(I\Listing\Workflow\Factory::class),
            $this->createMock(I\Listing\CharacteristicValue\Factory::class),
        ) extends NoUIFactory {
            public function __construct(
                protected I\Listing\Workflow\Factory $workflow_factory,
                protected I\Listing\CharacteristicValue\Factory $characteristic_value_factory,
            ) {
            }
            public function listing(): I\Listing\Factory
            {
                return new I\Listing\Factory(
                    $this->workflow_factory,
                    $this->characteristic_value_factory,
                    new I\Listing\Entity\Factory(),
                );
            }
            public function entity(): I\Entity\Factory
            {
                return new Entity\Factory();
            }
        };
    }

    public function testGridEntityListingFactory(): void
    {
        $this->assertInstanceOf(
            C\Listing\Entity\EntityListing::class,
            $this->getUIFactory()->listing()->entity()->grid($this->getEntityMapping())
        );
    }

    public function testGridEntityListingRendering(): void
    {
        $data = new class () implements C\Listing\Entity\DataRetrieval {
            protected $data = [1,2,3];

            public function getEntities(
                C\Listing\Entity\Mapping $mapping,
                ?Range $range,
                ?array $additional_parameters
            ): \Generator {
                foreach ($this->data as $entry) {
                    yield $mapping->map($entry);
                }
            }
        };

        $listing = $this->getUIFactory()->listing()->entity()
            ->grid($this->getEntityMapping())
            ->withData($data);

        $render = $this->getDefaultRenderer()->render($listing);
        $expected = <<<HTML
            <ul class="c-listing-entity-grid">
               <li>
                  <section aria-labelledby="id_1" class="c-entity__container">
                     <div class="c-entity__featured-headerbar l-bar__container">
                        <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
                           <div class="l-bar__group">
                              <div class="l-bar__element">
                                 <div id="id_1" class="c-entity__primary-identifier">primary</div>
                              </div>
                           </div>
                           <div class="c-entity__actions-container l-bar__group"></div>
                        </div>
                     </div>
                     <div class="c-entity__secondary-identifier --string ">secondary</div>
                  </section>
               </li>
               <li>
                  <section aria-labelledby="id_2" class="c-entity__container">
                     <div class="c-entity__featured-headerbar l-bar__container">
                        <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
                           <div class="l-bar__group">
                              <div class="l-bar__element">
                                 <div id="id_2" class="c-entity__primary-identifier">primary</div>
                              </div>
                           </div>
                           <div class="c-entity__actions-container l-bar__group"></div>
                        </div>
                     </div>
                     <div class="c-entity__secondary-identifier --string ">secondary</div>
                  </section>
               </li>
               <li>
                  <section aria-labelledby="id_3" class="c-entity__container">
                     <div class="c-entity__featured-headerbar l-bar__container">
                        <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
                           <div class="l-bar__group">
                              <div class="l-bar__element">
                                 <div id="id_3" class="c-entity__primary-identifier">primary</div>
                              </div>
                           </div>
                           <div class="c-entity__actions-container l-bar__group"></div>
                        </div>
                     </div>
                     <div class="c-entity__secondary-identifier --string ">secondary</div>
                  </section>
               </li>
            </ul>
            HTML;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($render)
        );
    }

    public function testGridEntityListingYieldingEntities(): void
    {
        $data = new class () implements C\Listing\Entity\DataRetrieval {
            protected $data = [1,2,3];

            public function getEntities(
                C\Listing\Entity\Mapping $mapping,
                ?Range $range,
                ?array $additional_parameters
            ): \Generator {
                foreach ($this->data as $entry) {
                    yield $mapping->map($entry);
                }
            }
        };

        $listing = $this->getUIFactory()->listing()->entity()
            ->grid($this->getEntityMapping())
            ->withData($data);

        $entities = iterator_to_array($listing->getEntities($this->getUIFactory()));

        $this->assertCount(3, $entities);

        $this->assertInstanceOf(C\Entity\Entity::class, array_pop($entities));
    }
}
