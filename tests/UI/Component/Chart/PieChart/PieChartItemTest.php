<?php

use ILIAS\Data\Color;
use ILIAS\UI\Implementation\Component\Chart\PieChart\PieChartItem;

/**
 * Class PieChartItemTest
 *
 * Test on pie chart item implementation.
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PieChartItemTest extends ILIAS_UI_TestBase {

	/**
	 * @return PieChartItem
	 */
	protected function getTestPieChartItemObject(): PieChartItem {
		return new PieChartItem("Test", 5, new Color(50, 50, 50), new Color(255, 255, 255));
	}


	/**
	 *
	 */
	public function test_get_value(): void {
		$pieChartItem = $this->getTestPieChartItemObject();

		$this->assertEquals($pieChartItem->getValue(), 5);
	}


	/**
	 *
	 */
	public function test_get_text_color(): void {
		$pieChartItem = $this->getTestPieChartItemObject();

		$this->assertEquals($pieChartItem->getTextColor()->asHex(), "#ffffff");
	}


	/**
	 *
	 */
	public function test_get_name(): void {
		$pieChartItem = $this->getTestPieChartItemObject();

		$this->assertEquals($pieChartItem->getName(), "Test");
	}


	/**
	 *
	 */
	public function test_get_color(): void {
		$pieChartItem = $this->getTestPieChartItemObject();

		$this->assertEquals($pieChartItem->getColor()->asHex(), "#323232");
	}
}
