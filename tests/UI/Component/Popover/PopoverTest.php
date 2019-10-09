<?php
use ILIAS\UI\Implementation\Component\Popover\Popover;
use \ILIAS\UI\Implementation as I;

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

/**
 * Class PopoverTest
 *
 * Tests on the Popover component implementation
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class PopoverTest extends ILIAS_UI_TestBase
{
    public function test_implements_interface()
    {
        $factory = new I\Component\Popover\Factory(new I\Component\SignalGenerator);
        $standard = $factory->standard(new DummyComponent());
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Standard", $standard);
        $listing = $factory->listing([new DummyComponent()]);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Popover\\Listing", $listing);
    }

    public function test_that_position_is_auto_by_default()
    {
        $factory = new I\Component\Popover\Factory(new I\Component\SignalGenerator);
        $popover = $factory->standard(new DummyComponent());
        $this->assertEquals(Popover::POS_AUTO, $popover->getPosition());
    }

    public function test_with_position()
    {
        $factory = new I\Component\Popover\Factory(new I\Component\SignalGenerator);
        $popover1 = $factory->standard(new DummyComponent());
        $popover2 = $popover1->withVerticalPosition();
        $popover3 = $popover2->withHorizontalPosition();
        $this->assertEquals(Popover::POS_AUTO, $popover1->getPosition());
        $this->assertEquals(Popover::POS_VERTICAL, $popover2->getPosition());
        $this->assertEquals(Popover::POS_HORIZONTAL, $popover3->getPosition());
        $this->assertEquals($popover1->getContent(), $popover2->getContent());
        $this->assertEquals($popover1->getContent(), $popover3->getContent());
    }

    public function test_render_standard()
    {
        $factory = new I\Component\Popover\Factory(new I\Component\SignalGenerator);
        $popover = $factory->standard(new I\Component\Legacy\Legacy('myContent'));
        $expected = $this->normalizeHTML($this->getExpectedStandardHTML('myContent'));
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($popover));
        $this->assertEquals($expected, $actual);
    }

    public function test_render_listing()
    {
        // TODO Listing not yet in framework core
        $this->assertTrue(true);
    }

    public function test_render_async()
    {
        $factory = new I\Component\Popover\Factory(new I\Component\SignalGenerator);
        $popover = $factory->standard(new I\Component\Legacy\Legacy('myContent'))->withAsyncContentUrl('/blub/');
        $this->assertEquals('', $this->getDefaultRenderer()->render($popover));
    }

    /**
     * @param string $content
     * @return string
     */
    protected function getExpectedStandardHTML($content)
    {
        return '<div class="il-standard-popover-content" style="display:none;" id="id_1">' . $content . '</div>';
    }
}
