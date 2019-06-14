<?php

require_once(__DIR__."/../../../Base.php");

use ILIAS\Data\Color;
use ILIAS\UI\Implementation\Component\Chart\PieChart\LegendEntry;
use ILIAS\UI\Implementation\Component\Chart\PieChart\PieChartItem;
use ILIAS\UI\Implementation\Component\Chart\PieChart\Section;

/**
 * Class SectionTest
 *
 * Test on section implementation.
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SectionTest extends ILIAS_UI_TestBase {

	/**
	 * @return Section
	 */
	protected function getTestSection(): Section {
		$pieChartItem = new PieChartItem("In Progress", 10, new Color(255, 255, 0), new Color(0, 0, 0));

		return new Section($pieChartItem, 50, 2, 1, 0);
	}


	/**
	 *
	 */
	public function test_with_text_color(): void {
		$section = $this->getTestSection();
		$testColor = new Color(120, 120, 120);
		$result = $section->withTextColor($testColor);

		$this->assertEquals($result->getTextColor()->asHex(), $testColor->asHex());
	}


	/**
	 *
	 */
	public function test_get_name(): void {
		$section = $this->getTestSection();

		$this->assertEquals($section->getName(), "In Progress");
	}


	/**
	 *
	 */
	public function test_get_value(): void {
		$section = $this->getTestSection();

		$this->assertEquals($section->getValue()->getValue(), 10);
	}


	/**
	 *
	 */
	public function test_get_percentage(): void {
		$section = $this->getTestSection();

		$this->assertEquals($section->getPercentage(), 20);
	}


	/**
	 *
	 */
	public function test_get_stroke_length(): void {
		$section = $this->getTestSection();

		$this->assertEquals($section->getStrokeLength(), 7.846214201647705);
	}


	/**
	 *
	 */
	public function test_get_offset(): void {
		$section = $this->getTestSection();

		$this->assertEquals($section->getOffset(), 0);
	}


	/**
	 *
	 */
	public function test_get_color(): void {
		$section = $this->getTestSection();
		$testColor = new Color(255, 255, 0);

		$this->assertEquals($section->getColor()->asHex(), $testColor->asHex());
	}


	/**
	 *
	 */
	public function test_get_legend(): void {
		$section = $this->getTestSection();

		$this->assertInstanceOf(LegendEntry::class, $section->getLegendEntry());
	}


	/**
	 *
	 */
	public function test_get_text_color(): void {
		$section = $this->getTestSection();
		$testColor = new Color(0, 0, 0);

		$this->assertEquals($section->getTextColor()->asHex(), $testColor->asHex());
	}
}
