<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class FactoryImplTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FactoryImplTest extends TestCase {

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
	protected function setUp() {
		parent::setUp();

		$this->identification = new IdentificationFactory();
		$this->provider = \Mockery::mock(StaticMainMenuProvider::class);

		$this->id = $this->identification->core($this->provider)->identifier('dummy');

		$this->factory = new MainMenuItemFactory();
	}


	public function testAvailableMethods() {
		$r = new ReflectionClass($this->factory);

		$methods = [];
		foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$methods[] = $method->getName();
		}
		sort($methods);
		$this->assertEquals(
			$methods, [
				        0 => 'complex',
				        1 => 'link',
				        2 => 'linkList',
				        3 => 'repositoryLink',
				        4 => 'separator',
				        5 => 'topLinkItem',
				        6 => 'topParentItem',
			        ]
		);
	}


	public function testTopItemConstraints() {
		$this->assertInstanceOf(isTopItem::class, $this->factory->topLinkItem($this->id));
		$this->assertInstanceOf(isTopItem::class, $this->factory->topParentItem($this->id));
		$this->assertNotInstanceOf(isTopItem::class, $this->factory->complex($this->id));
		$this->assertNotInstanceOf(isTopItem::class, $this->factory->link($this->id));
		$this->assertNotInstanceOf(isTopItem::class, $this->factory->repositoryLink($this->id));
		$this->assertNotInstanceOf(isTopItem::class, $this->factory->separator($this->id));
	}


	public function testChildConstraints() {
		$this->assertNotInstanceOf(isChild::class, $this->factory->topLinkItem($this->id));
		$this->assertNotInstanceOf(isChild::class, $this->factory->topParentItem($this->id));
		$this->assertInstanceOf(isChild::class, $this->factory->complex($this->id));
		$this->assertInstanceOf(isChild::class, $this->factory->link($this->id));
		$this->assertInstanceOf(isChild::class, $this->factory->repositoryLink($this->id));
		$this->assertInstanceOf(isChild::class, $this->factory->separator($this->id));
	}
}
