<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map\Map;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;

// require_once('./libs/composer/vendor/autoload.php');

/**
 * Class FactoryImplTest
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class MapTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var IdentificationFactory
     */
    protected $identification;
    /**
     * @var MainMenuItemFactory
     */
    protected $factory;
    /**
     * @var StaticMainMenuProvider
     */
    protected $provider;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->provider       = $this->getDummyProvider();
        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->factory        = new MainMenuItemFactory();
    }

    private function getId(string $id) : IdentificationInterface
    {
        return $this->identification->core($this->provider)->identifier($id);
    }

    public function testAddItem() : void
    {
        $map = new Map($this->factory);
        
        $p1 = $this->getId('parent_1');
        $p2 = $this->getId('parent_2');
        $p3 = $this->getId('parent_3');
        $map->addMultiple(
            ...[
                $this->factory->topParentItem($p1),
                $this->factory->topParentItem($p2),
                $this->factory->topParentItem($p3),
            ]
        );

        $p4 = $this->getId('parent_4');
        $map->add($this->factory->topParentItem($p4));

        $this->assertTrue($map->has());
        $this->assertSame(count(iterator_to_array($map->getAllFromFilter())), 4);
        $this->assertTrue($map->existsInFilter($p1));
        $this->assertTrue($map->existsInFilter($p2));
        $this->assertTrue($map->existsInFilter($p3));
        $this->assertTrue($map->existsInFilter($p4));
    }

    public function testFilterItems() : void
    {
        $map = new Map($this->factory);

        $p1 = $this->getId('parent_1');
        $p2 = $this->getId('parent_2');
        $p3 = $this->getId('parent_3');
        $p4 = $this->getId('parent_4');
        $map->addMultiple(
            ...[
                $this->factory->topParentItem($p1),
                $this->factory->topParentItem($p2),
                $this->factory->topParentItem($p3),
                $this->factory->topParentItem($p4)
            ]
        );

        $this->assertTrue($map->has());
        $this->assertSame(count(iterator_to_array($map->getAllFromFilter())), 4);

        $map->filter(static function () {
            return true;
        });

        $this->assertSame(count(iterator_to_array($map->getAllFromFilter())), 4);

        $map->filter(static function (isItem $i) {
            return $i->getProviderIdentification()->getInternalIdentifier() !== 'parent_1';
        });

        $this->assertSame(count(iterator_to_array($map->getAllFromFilter())), 3);
        $this->assertFalse($map->existsInFilter($p1));
        $this->assertTrue($map->existsInFilter($p2));
        $this->assertTrue($map->existsInFilter($p3));
        $this->assertTrue($map->existsInFilter($p4));

        $map->filter(static function () {
            return false;
        });
        $this->assertFalse($map->existsInFilter($p1));
        $this->assertFalse($map->existsInFilter($p2));
        $this->assertFalse($map->existsInFilter($p3));
        $this->assertFalse($map->existsInFilter($p4));

    }

    public function testSortingTopItems() : void
    {
        $map = new Map($this->factory);

        for ($x = 1; $x <= 10; $x++) {
            $map->add($this->factory->topParentItem($this->getId((string) $x))->withPosition(11 - $x));
        }

        $x = 10;
        foreach ($map->getAllFromFilter() as $i) {
            $this->assertSame($i->getPosition(), $x);
            $x--;
        }

        $map->sort();

        $generator = $map->getAllFromFilter();

        $one = static function () use ($generator): isItem {
            $i = $generator->current();
            $generator->next();
            return $i;
        };

        $i = $one();
        $this->assertSame('10', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(1, $i->getPosition());

        $i = $one();
        $this->assertSame('9', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(2, $i->getPosition());

        $i = $one();
        $this->assertSame('8', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(3, $i->getPosition());

        $i = $one();
        $this->assertSame('7', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(4, $i->getPosition());

        $i = $one();
        $this->assertSame('6', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(5, $i->getPosition());

        $i = $one();
        $this->assertSame('5', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(6, $i->getPosition());

        $i = $one();
        $this->assertSame('4', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(7, $i->getPosition());

        $i = $one();
        $this->assertSame('3', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(8, $i->getPosition());

        $i = $one();
        $this->assertSame('2', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(9, $i->getPosition());

        $i = $one();
        $this->assertSame('1', $i->getProviderIdentification()->getInternalIdentifier());
        $this->assertSame(10, $i->getPosition());

    }
    
    public function testSortingNestedItems()
    {
        $map = new Map($this->factory);
        /** @var TopParentItem $tp_1 */
        $tp_1 = $this->factory->topParentItem($this->getId('tp_1'))
                              ->withPosition(1);
        $tp_1_1 = $this->factory->link($this->getId('tp_1_1'))
                                ->withParent($tp_1->getProviderIdentification())
                                ->withPosition(1);
        $tp_1_2 = $this->factory->link($this->getId('tp_1_2'))
                                ->withParent($tp_1->getProviderIdentification())
                                ->withPosition(2);
        $tp_1 = $tp_1->withChildren([
            $tp_1_2,
            $tp_1_1,
        ]);
        $map->add($tp_1);
        $map->add($tp_1_2);
        $map->add($tp_1_1);
        
        $tp_2 = $this->factory->topParentItem($this->getId('tp_2'))
                              ->withPosition(2);
        $tp_2_1 = $this->factory->link($this->getId('tp_2_1'))
                                ->withParent($tp_2->getProviderIdentification())
                                ->withPosition(1);
        $tp_2_2 = $this->factory->link($this->getId('tp_2_2'))
                                ->withParent($tp_2->getProviderIdentification())
                                ->withPosition(2);
        $tp_2 = $tp_2->withChildren([
            $tp_2_2,
            $tp_2_1,
        ]);
        $map->add($tp_2);
        $map->add($tp_2_1);
        $map->add($tp_2_2);
        
        $map->sort();
        
        $this->assertEquals(1, $map->getSingleItemFromRaw($this->getId('tp_1'))->getPosition());
        $this->assertEquals(1, $map->getSingleItemFromRaw($this->getId('tp_1_1'))->getPosition());
        $this->assertEquals(2, $map->getSingleItemFromRaw($this->getId('tp_1_2'))->getPosition());
        $this->assertEquals(2, $map->getSingleItemFromRaw($this->getId('tp_2'))->getPosition());
        $this->assertEquals(1, $map->getSingleItemFromRaw($this->getId('tp_2_1'))->getPosition());
        $this->assertEquals(2, $map->getSingleItemFromRaw($this->getId('tp_2_2'))->getPosition());
        
        // check position in parent
        $get_tp_1 = $map->getSingleItemFromFilter($this->getId('tp_1'));
        $children_of_tp_1 = $get_tp_1->getChildren();
        $first = reset($children_of_tp_1);
        $this->assertEquals($tp_1_1, $first);
        $this->assertEquals(1, $first->getPosition());
        
        $get_tp_2 = $map->getSingleItemFromFilter($this->getId('tp_2'));
        $children_of_tp_2 = $get_tp_2->getChildren();
        $first = reset($children_of_tp_2);
        $this->assertEquals($tp_2_1, $first);
        $this->assertEquals(1, $first->getPosition());
    }
    
    public function testSamePositionResolution()
    {
        $map = new Map($this->factory);
        /** @var TopParentItem $tp_1 */
        $tp_1 = $this->factory->topParentItem($this->getId('tp_1'))
                              ->withPosition(1);
        $tp_1_1 = $this->factory->link($this->getId('tp_1_1'))
                                ->withParent($tp_1->getProviderIdentification())
                                ->withPosition(1);
        $tp_1_2 = $this->factory->link($this->getId('tp_1_2'))
                                ->withParent($tp_1->getProviderIdentification())
                                ->withPosition(1);
        $tp_1 = $tp_1->withChildren([
            $tp_1_2,
            $tp_1_1,
        ]);
        $map->add($tp_1);
        $map->add($tp_1_2);
        $map->add($tp_1_1);
        $map->sort();
        $item = $map->getSingleItemFromFilter($this->getId('tp_1'));
        $this->assertEquals(2, count($item->getChildren()));
    }
    
    private function getDummyProvider()
    {
        return new class implements StaticMainMenuProvider {
            public function getAllIdentifications() : array
            {
                return [];
            }

            public function getFullyQualifiedClassName() : string
            {
                return 'Provider';
            }

            public function getProviderNameForPresentation() : string
            {
                return 'Provider';
            }

            public function getStaticTopItems() : array
            {
                return [];
            }

            public function getStaticSubItems() : array
            {
                return [];
            }

            public function provideTypeInformation() : TypeInformationCollection
            {
                return new TypeInformationCollection();
            }
        };
    }
}
