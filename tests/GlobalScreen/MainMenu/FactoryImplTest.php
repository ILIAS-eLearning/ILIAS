<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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
    use MockeryPHPUnitIntegration;
    /**
     * @var IdentificationInterface
     */
    protected $id;
    /**
     * @var StaticMainMenuProvider
     */
    protected $provider;
    /**
     * @var IdentificationFactory
     */
    protected $identification;
    /**
     * @var MainMenuItemFactory
     */
    protected $factory;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->provider = \Mockery::mock(StaticMainMenuProvider::class);
        $this->provider->shouldReceive('getProviderNameForPresentation')->andReturn('Provider');

        $this->id = $this->identification->core($this->provider)->identifier('dummy');

        $this->factory = new MainMenuItemFactory();
    }


    public function testAvailableMethods()
    {
        $r = new ReflectionClass($this->factory);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertEquals(
            $methods,
            [
                0 => 'complex',
                1 => 'custom',
                2 => 'link',
                3 => 'linkList',
                4 => 'repositoryLink',
                5 => 'separator',
                6 => 'topLinkItem',
                7 => 'topParentItem',
            ]
        );
    }


    public function testTopItemConstraints()
    {
        $this->assertInstanceOf(isTopItem::class, $this->factory->topLinkItem($this->id));
        $this->assertInstanceOf(isTopItem::class, $this->factory->topParentItem($this->id));
        $this->assertNotInstanceOf(isTopItem::class, $this->factory->complex($this->id));
        $this->assertNotInstanceOf(isTopItem::class, $this->factory->link($this->id));
        $this->assertNotInstanceOf(isTopItem::class, $this->factory->repositoryLink($this->id));
        $this->assertNotInstanceOf(isTopItem::class, $this->factory->separator($this->id));
    }


    public function testChildConstraints()
    {
        $this->assertNotInstanceOf(isChild::class, $this->factory->topLinkItem($this->id));
        $this->assertNotInstanceOf(isChild::class, $this->factory->topParentItem($this->id));
        $this->assertInstanceOf(isChild::class, $this->factory->complex($this->id));
        $this->assertInstanceOf(isChild::class, $this->factory->link($this->id));
        $this->assertInstanceOf(isChild::class, $this->factory->repositoryLink($this->id));
        $this->assertInstanceOf(isChild::class, $this->factory->separator($this->id));
    }
}
