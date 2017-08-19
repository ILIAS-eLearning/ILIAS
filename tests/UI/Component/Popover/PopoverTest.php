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
		$standard = $factory->popover()->standard(new DummyComponent());
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Standard", $standard);
		$listing = $factory->popover()->listing([new DummyComponent()]);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Listing", $listing);
	}

	public function test_that_position_is_auto_by_default() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover()->standard(new DummyComponent());
		$this->assertEquals(Popover::POS_AUTO, $popover->getPosition());
	}

	public function test_with_position() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover1 = $factory->popover()->standard(new DummyComponent());
		$popover2 = $popover1->withVerticalPosition();
		$popover3 = $popover2->withHorizontalPosition();
		$this->assertEquals(Popover::POS_AUTO, $popover1->getPosition());
		$this->assertEquals(Popover::POS_VERTICAL, $popover2->getPosition());
		$this->assertEquals(Popover::POS_HORIZONTAL, $popover3->getPosition());
		$this->assertEquals($popover1->getContent(), $popover2->getContent());
		$this->assertEquals($popover1->getContent(), $popover3->getContent());
	}

	public function test_render_standard() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover()->standard($factory->legacy('myContent'));
		$expected = $this->normalizeHTML($this->getExpectedStandardHTML('myContent'));
		$actual = $this->normalizeHTML($this->getDefaultRenderer()->render($popover));
		$this->assertEquals($expected, $actual);
	}

	public function test_render_listing() {
		// TODO Listing not yet in framework core
		$this->assertTrue(true);
	}

	public function test_render_async() {
		$factory = new \ILIAS\UI\Implementation\Factory();
		$popover = $factory->popover()->standard($factory->legacy('myContent'))->withAsyncContentUrl('/blub/');
		$this->assertEquals('', $this->getDefaultRenderer()->render($popover));
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function getExpectedStandardHTML($content) {
		return '<div class="il-standard-popover-content" style="display:none;" id="id_1">' . $content . '</div>';
	}

}
