<?php

require_once(__DIR__."/../../../Base.php");

use ILIAS\UI\Implementation\Component\Chart\PieChart\SectionValue;

/**
 * Class SectionValueTest
 *
 * Test on section value implementation.
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SectionValueTest extends ILIAS_UI_TestBase {

	/**
	 * @return SectionValue
	 */
	protected function getTestSectionValue(): SectionValue {
		return new SectionValue(12, 18.830914083954, - 4.7077285209886, 40);
	}


	/**
	 *
	 */
	public function test_get_y_percentage(): void {
		$sectionValue = $this->getTestSectionValue();

		$this->assertEquals($sectionValue->getYPercentage(), 71.18911417633495);
	}


	/**
	 *
	 */
	public function test_get_text_size(): void {
		$sectionValue = $this->getTestSectionValue();

		$this->assertEquals($sectionValue->getTextSize(), 3);
	}


	/**
	 *
	 */
	public function test_get_value(): void {
		$sectionValue = $this->getTestSectionValue();

		$this->assertEquals($sectionValue->getValue(), 12);
	}


	/**
	 *
	 */
	public function test_get_x_percentage(): void {
		$sectionValue = $this->getTestSectionValue();

		$this->assertEquals($sectionValue->getXPercentage(), 16.394776138393137);
	}
}
