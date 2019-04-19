<?php declare(strict_types=1);

use ILIAS\UI\Component\Tooltip\Tooltip;
use \ILIAS\UI\Implementation as I;

require_once __DIR__ . '/../../../../libs/composer/vendor/autoload.php';
require_once __DIR__ . '/../../Base.php';

/**
 * Class TooltipTest
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class TooltipTest extends ILIAS_UI_TestBase
{
	/**
	 *
	 */
	public function testPlacingATooltipWorksAsExpected()
	{
		$factory  = new I\Component\Tooltip\Factory(new I\Component\SignalGenerator);

		$tooltip1 = $factory->standard([new I\Component\Legacy\Legacy('phpunit')]);
		$tooltip2 = $tooltip1->withPlacementLeft();
		$tooltip3 = $tooltip2->withPlacementRight();
		$tooltip4 = $tooltip3->withPlacemenBottom();

		$this->assertEquals(Tooltip::PLACEMENT_TOP, $tooltip1->getPlacement());
		$this->assertEquals(Tooltip::PLACEMENT_LEFT, $tooltip2->getPlacement());
		$this->assertEquals(Tooltip::PLACEMENT_RIGHT, $tooltip3->getPlacement());
		$this->assertEquals(Tooltip::PLACEMENT_BOTTOM, $tooltip4->getPlacement());
		$this->assertEquals($tooltip1->contents(), $tooltip2->contents());
		$this->assertEquals($tooltip1->contents(), $tooltip3->contents());
		$this->assertEquals($tooltip1->contents(), $tooltip4->contents());
	}
}
