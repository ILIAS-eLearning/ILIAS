<?php
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

/**
 * Class PopoverTest
 *
 * Tests on the Popover component implementation
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class PopoverTest extends ILIAS_UI_TestBase {

	public function test_implements_interface() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle',  new DummyComponent());
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Popover", $popover);
	}

	public function test_that_position_is_auto_by_default() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle', new DummyComponent());
		$this->assertEquals('auto', $popover->getPosition());
	}

	public function test_with_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover1 = $factory->popover('myTitle', new DummyComponent());
		$popover2 = $popover1->withPosition('vertical');
		$popover3 = $popover2->withPosition('horizontal');
		$this->assertEquals('auto', $popover1->getPosition());
		$this->assertEquals('vertical', $popover2->getPosition());
		$this->assertEquals('horizontal', $popover3->getPosition());
	}

	public function test_failing_on_invalid_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$this->setExpectedException(InvalidArgumentException::class);
		$factory->popover('myTitle', new DummyComponent())
			->withPosition('hozirontal');
	}
}
