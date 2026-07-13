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

use ILIAS\UI\Implementation\Component\Listing;
use ILIAS\UI\Implementation\Component\Entity;
use ILIAS\UI\Component as I;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Range;

class GridEntityListingTest extends ILIAS_UI_TestBase
{
    public function getEntityMapping(): I\Listing\Entity\RecordToEntity
    {
        return new class () implements I\Listing\Entity\RecordToEntity {
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
        return new class () extends NoUIFactory {
            public function listing(): I\Listing\Factory
            {
                return new Listing\Factory();
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
            I\Listing\Entity\EntityListing::class,
            $this->getUIFactory()->listing()->entity()->grid($this->getEntityMapping())
        );
    }

    public function testGridEntityListingRendering(): void
    {
        $data = new class () implements I\Listing\Entity\DataRetrieval {
            protected $data = [1,2,3];

            public function getEntities(
                I\Listing\Entity\Mapping $mapping,
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
        $data = new class () implements I\Listing\Entity\DataRetrieval {
            protected $data = [1,2,3];

            public function getEntities(
                I\Listing\Entity\Mapping $mapping,
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

        $this->assertInstanceOf(I\Entity\Entity::class, array_pop($entities));
    }
}
