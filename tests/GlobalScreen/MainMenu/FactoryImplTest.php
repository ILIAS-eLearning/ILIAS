<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class FactoryImplTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class FactoryImplTest extends TestCase
{
    protected IdentificationInterface $id;
    protected StaticMainMenuProvider $provider;
    protected IdentificationFactory $identification;
    protected MainMenuItemFactory $factory;


    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->provider = $this->getMockBuilder(StaticMainMenuProvider::class)->getMock();

        $this->id = $this->identification->core($this->provider)->identifier('dummy');

        $this->factory = new MainMenuItemFactory();
    }


    public function testAvailableMethods() : void
    {
        $r = new ReflectionClass($this->factory);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertEquals(
            [
                0 => 'complex',
                1 => 'custom',
                2 => 'link',
                3 => 'linkList',
                4 => 'repositoryLink',
                5 => 'separator',
                6 => 'topLinkItem',
                7 => 'topParentItem',
            ],
            $methods
        );
    }


    public function testInterchangeableContraints() : void
    {
        $this->assertInstanceOf(isInterchangeableItem::class, $this->factory->topLinkItem($this->id));
        $this->assertNotInstanceOf(isInterchangeableItem::class, $this->factory->topParentItem($this->id));
        $this->assertInstanceOf(isInterchangeableItem::class, $this->factory->complex($this->id));
        $this->assertInstanceOf(isInterchangeableItem::class, $this->factory->link($this->id));
        $this->assertInstanceOf(isInterchangeableItem::class, $this->factory->repositoryLink($this->id));
        $this->assertNotInstanceOf(isInterchangeableItem::class, $this->factory->separator($this->id));
    }
}
