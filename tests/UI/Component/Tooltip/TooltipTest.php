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
	public function testInitialPlacementIsAlwaysTop(): Standard
	{
		$factory  = new I\Component\Tooltip\Factory(new I\Component\SignalGenerator);

		$tooltip = $factory->standard([new I\Component\Legacy\Legacy('phpunit')]);

		$this->assertEquals(Tooltip::PLACEMENT_TOP, $tooltip->getPlacement());

		return $tooltip;
	}

	/**
	 * @param Standard $tooltip
	 * @depends testInitialPlacementIsAlwaysTop
	 */
	public function testRenderedHtmlMatchesExptectedDom(Standard $tooltip)
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
	public function testPlacingATooltipWorksAsExpected(Standard $tooltip)
	{
		$tooltip2 = $tooltip->withPlacementLeft();
		$tooltip3 = $tooltip2->withPlacementRight();
		$tooltip4 = $tooltip3->withPlacemenBottom();

		$this->assertEquals(Tooltip::PLACEMENT_LEFT, $tooltip2->getPlacement());
		$this->assertEquals(Tooltip::PLACEMENT_RIGHT, $tooltip3->getPlacement());
		$this->assertEquals(Tooltip::PLACEMENT_BOTTOM, $tooltip4->getPlacement());
		$this->assertEquals($tooltip->contents(), $tooltip2->contents());
		$this->assertEquals($tooltip->contents(), $tooltip3->contents());
		$this->assertEquals($tooltip->contents(), $tooltip4->contents());
	}
}
