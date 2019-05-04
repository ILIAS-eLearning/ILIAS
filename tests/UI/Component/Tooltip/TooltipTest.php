<?php declare(strict_types=1);

use ILIAS\UI\Component\Tooltip\Standard;
use ILIAS\UI\Component\Tooltip\Tooltip;
use \ILIAS\UI\Implementation as I;

require_once __DIR__ . '/../../../../libs/composer/vendor/autoload.php';
require_once __DIR__ . '/../../Base.php';

/**
 *
 */
class TooltipTest extends ILIAS_UI_TestBase
{

	/**
	 * @return Standard
	 */
	public function testTooltipsAreInitiallyPositionedAutomatically(): Standard
	{
		$factory  = new I\Component\Tooltip\Factory(new I\Component\SignalGenerator);

		$tooltip = $factory->standard([new I\Component\Legacy\Legacy('phpunit')]);

		$this->assertEquals(Tooltip::POSITION_AUTO, $tooltip->getPosition());

		return $tooltip;
	}

	/**
	 * @param Standard $tooltip
	 * @depends testInitialPlacementIsAlwaysTop
	 */
	public function testRenderedHtmlMatchesExpectedDom(Standard $tooltip)
	{
		$expected = <<<EOT
<div class="il-standard-tooltip" style="display:none;" id="id_1">
	<div class="il-standard-tooltip-content">phpunit</div>
</div>
EOT;
		$this->assertEquals(
			$this->normalizeHTML($expected),
			$this->normalizeHTML($this->getDefaultRenderer()->render($tooltip))
		);
	}

	/**
	 * @depends testInitialPlacementIsAlwaysTop
	 * @param Standard $tooltip
	 */
	public function testPositioningATooltipWorksAsExpected(Standard $tooltip)
	{
		$tooltip2 = $tooltip->withLeftPosition();
		$tooltip3 = $tooltip2->withRightPosition();
		$tooltip4 = $tooltip3->withBottomPosition();
		$tooltip5 = $tooltip3->withTopPosition();

		$this->assertEquals(Tooltip::POSITION_LEFT, $tooltip2->getPosition());
		$this->assertEquals(Tooltip::POSITION_RIGHT, $tooltip3->getPosition());
		$this->assertEquals(Tooltip::POSITION_BOTTOM, $tooltip4->getPosition());
		$this->assertEquals(Tooltip::POSITION_TOP, $tooltip5->getPosition());
		$this->assertEquals($tooltip->contents(), $tooltip2->contents());
		$this->assertEquals($tooltip->contents(), $tooltip3->contents());
		$this->assertEquals($tooltip->contents(), $tooltip4->contents());
		$this->assertEquals($tooltip->contents(), $tooltip5->contents());
	}
}
