<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class CollectorTest
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp() : void
    {
        parent::setUp();
    }

    public function testBasic() : void
    {
        $this->assertTrue(true);
        return; // WIP
        $collector = new MainMenuMainCollector([$this->getDummyProvider()], $this->getItemInformation());
        $collector->collectOnce();

        $this->assertTrue($collector->hasItems());
    }

    private function getItemInformation() : ItemInformation
    {
        return new class implements ItemInformation {
            public function isItemActive(isItem $item) : bool
            {
                return true;
            }

            public function customPosition(isItem $item) : isItem
            {
                return $item;
            }

            public function customTranslationForUser(hasTitle $item) : hasTitle
            {
                return $item;
            }

            public function getParent(isChild $item) : IdentificationInterface
            {
                return $item->getParent();
            }

            public function customSymbol(hasSymbol $item) : hasSymbol
            {
                return $item;
            }
        };
    }

    private function getDummyProvider() : StaticMainMenuProvider
    {
        return new class implements StaticMainMenuProvider {
            /**
             * @var IdentificationInterface[]
             */
            private $p_identifications;
            /**
             * @var IdentificationInterface[]
             */
            protected $c_identifications;

            public function __construct()
            {
                $if = new IdentificationFactory(new NullProviderFactory());
                $iff = function (string $id) use ($if) {
                    return $if->core($this)->identifier($id);
                };
                $this->factory = new MainMenuItemFactory();
                $this->p_identifications = [];
                $this->c_identifications = [];
                $this->type_information = new TypeInformationCollection();
                $this->type_information->add();

                for ($x = 1; $x < 5; $x++) {
                    $this->p_identifications['id_' . $x] = $iff('id_' . $x);
                    for ($y = 1; $y < 5; $y++) {
                        $this->c_identifications[] = $iff('id_' . $x . '/' . $y);
                    }
                }
            }

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
                $items = [];
                foreach ($this->p_identifications as $if) {
                    $items[] = $this->factory->topParentItem($if)->withTitle($if->getInternalIdentifier());
                }
                return $items;
            }

            public function getStaticSubItems() : array
            {
                $items = [];
                foreach ($this->c_identifications as $if) {
                    $identifier = $if->getInternalIdentifier();
                    $items[] = $this->factory->link($if)->withTitle($identifier)->withParent($this->p_identifications[strstr($identifier, '/', true)]);
                }
                return $items;
            }

            public function provideTypeInformation() : TypeInformationCollection
            {
                return $this->type_information;
            }
        };
    }
}
