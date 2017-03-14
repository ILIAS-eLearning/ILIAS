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
		$popover = $factory->popover('myTitle', 'myText');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Popover", $popover);
	}

	public function test_get_title() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle', 'myText');
		$this->assertEquals('myTitle', $popover->getTitle());
	}

	public function test_get_text() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle', 'myText');
		$this->assertEquals('myText', $popover->getText());
	}

	public function test_that_position_is_auto_by_default() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle', 'myText');
		$this->assertEquals('auto', $popover->getPosition());
	}

	public function test_get_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover('myTitle', 'myText', 'top');
		$this->assertEquals('top', $popover->getPosition());
	}

	public function test_failing_on_invalid_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$this->setExpectedException(InvalidArgumentException::class);
		$factory->popover('myTitle', 'myText', 'not-existing-position');
	}
}
