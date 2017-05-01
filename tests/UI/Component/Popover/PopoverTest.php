<?php
use ILIAS\UI\Implementation\Component\Popover\Popover;

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
		$popover = $factory->popover(new DummyComponent());
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Popover", $popover);
	}

	public function test_that_position_is_auto_by_default() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover(new DummyComponent());
		$this->assertEquals(Popover::POS_AUTO, $popover->getPosition());
	}

	public function test_with_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover1 = $factory->popover(new DummyComponent());
		$popover2 = $popover1->withVerticalPosition();
		$popover3 = $popover2->withHorizontalPosition();
		$this->assertEquals(Popover::POS_AUTO, $popover1->getPosition());
		$this->assertEquals(Popover::POS_VERTICAL, $popover2->getPosition());
		$this->assertEquals(Popover::POS_HORIZONTAL, $popover3->getPosition());
		$this->assertEquals($popover1->getContent(), $popover2->getContent());
		$this->assertEquals($popover1->getContent(), $popover3->getContent());
	}
}
