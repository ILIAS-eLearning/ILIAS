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

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use PHPUnit\Framework\TestCase;

require_once('./vendor/composer/vendor/autoload.php');

/**
 * Class CollectorTest
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorTest extends TestCase
{
    private MainMenuItemFactory $factory;
    private mixed $dic_backup;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $this->dic_backup = $DIC ?? null;
        $DIC = $this->createStub(Container::class);
        $DIC->method('ui')->willReturn($this->createStub(UIServices::class));

        $this->factory = new MainMenuItemFactory();
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
        parent::tearDown();
    }

    public function testBasic(): void
    {
        $provider = $this->getDummyProvider();
        $renderer = $this->createStub(TypeRenderer::class);
        foreach ([TopParentItem::class, Link::class] as $type) {
            $provider->provideTypeInformation()->add(new TypeInformation($type, $type, $renderer));
        }

        $collector = new MainMenuMainCollector([$provider], $this->factory, $this->getItemInformation());
        $collector->collectOnce();

        $this->assertTrue($collector->hasItems());
        $this->assertTrue($collector->hasVisibleItems());
        $top_items = iterator_to_array($collector->getItemsForUIRepresentation(), false);
        $this->assertCount(4, $top_items);
        foreach ($top_items as $top_item) {
            $this->assertInstanceOf(TopParentItem::class, $top_item);
            $this->assertCount(4, $top_item->getChildren());
        }
    }

    private function getItemInformation(): ItemInformation
    {
        return new class () implements ItemInformation {
            public function isItemActive(isItem $item): bool
            {
                return true;
            }

            public function customPosition(isItem $item): isItem
            {
                return $item;
            }

            public function customTranslationForUser(hasTitle $item): hasTitle
            {
                return $item;
            }

            public function getParent(isItem $item): IdentificationInterface
            {
                return $item instanceof isChild ? $item->getParent() : $item->getProviderIdentification();
            }

            public function customSymbol(hasSymbol $item): hasSymbol
            {
                return $item;
            }
        };
    }

    private function getDummyProvider(): StaticMainMenuProvider
    {
        return new class ($this->factory) implements StaticMainMenuProvider {
            /**
             * @var IdentificationInterface[]
             */
            private array $p_identifications = [];
            /**
             * @var IdentificationInterface[]
             */
            private array $c_identifications = [];
            private TypeInformationCollection $type_information;

            public function __construct(private MainMenuItemFactory $factory)
            {
                $if = new IdentificationFactory(new NullProviderFactory());
                $iff = (fn(string $id): IdentificationInterface => $if->core($this)->identifier($id));
                $this->type_information = new TypeInformationCollection();

                for ($x = 1; $x < 5; $x++) {
                    $this->p_identifications['id_' . $x] = $iff('id_' . $x);
                    for ($y = 1; $y < 5; $y++) {
                        $this->c_identifications[] = $iff('id_' . $x . '/' . $y);
                    }
                }
            }

            public function getAllIdentifications(): array
            {
                return [];
            }

            public function getFullyQualifiedClassName(): string
            {
                return 'Provider';
            }

            public function getProviderNameForPresentation(): string
            {
                return 'Provider';
            }

            public function getStaticTopItems(): array
            {
                $items = [];
                foreach ($this->p_identifications as $if) {
                    $items[] = $this->factory->topParentItem($if)->withTitle($if->getInternalIdentifier());
                }
                return $items;
            }

            public function getStaticSubItems(): array
            {
                $items = [];
                foreach ($this->c_identifications as $if) {
                    $identifier = $if->getInternalIdentifier();
                    $items[] = $this->factory->link($if)
                                             ->withTitle($identifier)
                                             ->withAction('#')
                                             ->withParent($this->p_identifications[strstr($identifier, '/', true)]);
                }
                return $items;
            }

            public function provideTypeInformation(): TypeInformationCollection
            {
                return $this->type_information;
            }
        };
    }
}
