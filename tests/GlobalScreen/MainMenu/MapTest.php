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

require_once('./libs/composer/vendor/autoload.php');

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
        $map = new Map();

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
        $map = new Map();

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
        $map = new Map();

        for ($x = 0; $x < 10; $x++) {
            $map->add($this->factory->topParentItem($this->getId('parent_' . $x))->withPosition(10 - $x));
        }

        $x = 10;
        foreach ($map->getAllFromFilter() as $i) {
            $this->assertSame($i->getPosition(), $x);
            $x--;
        }

        $map->sort();

        $x = 1;
        foreach ($map->getAllFromFilter() as $i) {
            $this->assertSame($i->getPosition(), $x);
            $x++;
        }
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
