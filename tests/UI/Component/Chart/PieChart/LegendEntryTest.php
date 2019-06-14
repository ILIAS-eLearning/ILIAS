<?php

require_once(__DIR__."/../../../Base.php");

use ILIAS\UI\Implementation\Component\Chart\PieChart\LegendEntry;

/**
 * Class LegendEntryTest
 *
 * Test on legend entry implementation.
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class LegendEntryTest extends ILIAS_UI_TestBase {

	/**
	 * @return LegendEntry
	 */
	protected function getTestLegendEntry(): LegendEntry {
		return new LegendEntry("Test", 5, 2);
	}


	/**
	 *
	 */
	public function test_get_y_percentage(): void {
		$legendEntry = $this->getTestLegendEntry();

		$this->assertEquals($legendEntry->getYPercentage(), 36.666666666666664);
	}


	/**
	 *
	 */
	public function test_get_text_y_percentage(): void {
		$legendEntry = $this->getTestLegendEntry();

		$this->assertEquals($legendEntry->getTextYPercentage(), 41.166666666667);
	}


	/**
	 *
	 */
	public function test_get_square_size(): void {
		$legendEntry = $this->getTestLegendEntry();

		$this->assertEquals($legendEntry->getSquareSize(), 2);
	}


	/**
	 *
	 */
	public function test_get_text_size(): void {
		$legendEntry = $this->getTestLegendEntry();

		$this->assertEquals($legendEntry->getTextSize(), 1.5);
	}


	/**
	 *
	 */
	public function test_get_title(): void {
		$legendEntry = $this->getTestLegendEntry();

		$this->assertEquals($legendEntry->getTitle(), "Test");
	}
}
