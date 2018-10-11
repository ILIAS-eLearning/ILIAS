<?php

namespace ILIAS\GlobalScreen\MainMenu;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class FactoryTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FactoryTest extends TestCase {

	use MockeryPHPUnitIntegration;
	/**
	 * @var MainMenuItemFactory
	 */
	protected $factory;


	/**
	 * @inheritDoc
	 */
	protected function setUp() {
		parent::setUp();

		$this->factory = new MainMenuItemFactory();
	}
}
