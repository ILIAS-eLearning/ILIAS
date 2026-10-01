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
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use PHPUnit\Framework\TestCase;

class MainMenuMainCollectorTest extends TestCase
{
    private IdentificationFactory $identification;
    private MainMenuItemFactory $factory;
    private mixed $dic_backup;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $this->dic_backup = $DIC ?? null;
        $DIC = $this->createStub(Container::class);
        $DIC->method('ui')->willReturn($this->createStub(UIServices::class));

        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->factory = new MainMenuItemFactory();
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
        parent::tearDown();
    }

    public function testDeactivatedItemIsOnlyRemovedFromFilteredItems(): void
    {
        $provider = $this->getProvider();
        $top = $this->identification->core($provider)->identifier('top');
        $top_without_active_children = $this->identification->core($provider)->identifier('top_2');
        $active = $this->identification->core($provider)->identifier('active');
        $inactive = $this->identification->core($provider)->identifier('inactive');
        $inactive_2 = $this->identification->core($provider)->identifier('inactive_2');

        $provider->top_items = [
            $this->factory->topParentItem($top)->withTitle('Top'),
            $this->factory->topParentItem($top_without_active_children)->withTitle('Top 2'),
        ];
        $provider->sub_items = [
            $this->factory->link($active)->withTitle('Active')->withAction('#')->withParent($top),
            $this->factory->link($inactive)->withTitle('Inactive')->withAction('#')->withParent($top),
            $this->factory->link($inactive_2)->withTitle('Inactive 2')->withAction('#')->withParent(
                $top_without_active_children
            ),
        ];
        $renderer = $this->createStub(TypeRenderer::class);
        foreach ([TopParentItem::class, Link::class] as $type) {
            $provider->type_information->add(new TypeInformation($type, $type, $renderer));
        }

        $collector = new MainMenuMainCollector(
            [$provider],
            $this->factory,
            $this->getInformation($inactive, $inactive_2)
        );
        $collector->collectOnce();

        // the frontend must not show deactivated items (0046085), getRawItems() returns the filtered items
        $filtered = $this->serialize(...iterator_to_array($collector->getRawItems(), false));
        $this->assertContains($active->serialize(), $filtered);
        $this->assertNotContains($inactive->serialize(), $filtered);
        $this->assertNotContains($inactive_2->serialize(), $filtered);

        $parent = $collector->getSingleItemFromFilter($top);
        $this->assertSame([$active->serialize()], $this->serialize(...$parent->getChildren()));
        $this->assertSame(2, $parent->getAmountOfChildren());

        // a top item without any active child is not shown (0045310)
        $shown = $this->serialize(...iterator_to_array($collector->getItemsForUIRepresentation(), false));
        $this->assertSame([$top->serialize()], $shown);

        // the administration reads the raw item, it must stay available to be reactivated (0047459)
        $raw_item = $collector->getSingleItemFromRaw($inactive);
        $this->assertInstanceOf(Link::class, $raw_item);
        $this->assertTrue($raw_item->isAvailable());
    }

    /**
     * @return string[]
     */
    private function serialize(isItem ...$items): array
    {
        return array_values(
            array_map(
                static fn(isItem $item): string => $item->getProviderIdentification()->serialize(),
                $items
            )
        );
    }

    private function getInformation(IdentificationInterface ...$inactive): ItemInformation
    {
        return new class (...$inactive) implements ItemInformation {
            private array $inactive;

            public function __construct(IdentificationInterface ...$inactive)
            {
                $this->inactive = array_map(
                    static fn(IdentificationInterface $id): string => $id->serialize(),
                    $inactive
                );
            }

            public function isItemActive(isItem $item): bool
            {
                return !in_array($item->getProviderIdentification()->serialize(), $this->inactive, true);
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

    private function getProvider(): StaticMainMenuProvider
    {
        return new class () implements StaticMainMenuProvider {
            public array $top_items = [];
            public array $sub_items = [];
            public TypeInformationCollection $type_information;

            public function __construct()
            {
                $this->type_information = new TypeInformationCollection();
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
                return $this->top_items;
            }

            public function getStaticSubItems(): array
            {
                return $this->sub_items;
            }

            public function provideTypeInformation(): TypeInformationCollection
            {
                return $this->type_information;
            }
        };
    }
}
