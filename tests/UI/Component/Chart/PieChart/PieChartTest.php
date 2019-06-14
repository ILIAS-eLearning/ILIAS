<?php

use ILIAS\Data\Color;
use ILIAS\UI\Implementation\Component\Chart\PieChart\PieChart;
use ILIAS\UI\Implementation\Component\Chart\PieChart\PieChartItem;
use ILIAS\UI\Implementation\Component\Chart\PieChart\Section;

/**
 * Class PieChartTest
 *
 * Test on pie chart implementation.
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PieChartTest extends ILIAS_UI_TestBase {

	/**
	 * @return PieChartItem[]
	 */
	protected function getTestArray(): array {
		return [
			new PieChartItem("In Progress", 1, new Color(255, 255, 0)),
			new PieChartItem("Done", 2, new Color(0, 255, 0))
		];
	}


	/**
	 * @return PieChart
	 */
	protected function getTestObject(): PieChart {
		return new PieChart($this->getTestArray());
	}


	/**
	 *
	 */
	public function test_get_sections(): void {
		$c = $this->getTestObject();
		$result = $c->getSections();
		$testColor = new Color(255, 255, 0);

		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertInstanceOf(Section::class, $result[0]);
		$this->assertEquals("In Progress", $result[0]->getName());
		$this->assertEquals(1, $result[0]->getValue()->getValue());
		$this->assertEquals($testColor->asHex(), $result[0]->getColor()->asHex());
	}


	/**
	 *
	 */
	public function test_get_total_value(): void {
		$c = $this->getTestObject();

		$result = $c->getTotalValue();

		$this->assertEquals(3, $result);
	}


	/**
	 *
	 */
	public function test_is_values_in_legend(): void {
		$c = $this->getTestObject();

		$result = $c->isValuesInLegend();

		$this->assertTrue(!$result);
	}


	/**
	 *
	 */
	public function test_with_values_in_legend(): void {
		$c = $this->getTestObject();

		$result = $c->withValuesInLegend(true);

		$this->assertTrue($result->isValuesInLegend());
	}


	/**
	 *
	 */
	public function test_render(): void {
		$c = $this->getTestObject();
		$html = $this->getDefaultRenderer()->render($c);

		/** @noinspection CssInvalidPropertyValue */
		$expected_html = <<<EOT
		<svg viewBox="0 0 63 32" overflow="auto">
	
	<circle class="il-chart-pie-chart-section" r="12.5%" cx="25%" cy="50%" style="stroke-dasharray: 13.077023669413 100; stroke: #ffff00; stroke-dashoffset: -0"></circle>
	
	<circle class="il-chart-pie-chart-section" r="12.5%" cx="25%" cy="50%" style="stroke-dasharray: 26.154047338826 100; stroke: #00ff00; stroke-dashoffset: -13.077023669413"></circle>
	
	
	<rect fill="#00ff00" height="2" width="2" x="50%" y="63.333333333333%"></rect>
	<text x="55%" y="67.833333333333%" font-size="1.5">Done</text>
	
	
	<text text-anchor="middle" dominant-baseline="central" font-size="3" x="31.75%" y="73.815698604072%" fill="#000000">1</text>
	
	<text text-anchor="middle" dominant-baseline="central" font-size="3" x="18.25%" y="26.184301395928%" fill="#000000">2</text>
	
	
	<circle class="il-chart-pie-chart-total-circle" r="9%" cx="25%" cy="50%"></circle>
	<text x="25%" y="50%" text-anchor="middle" dominant-baseline="central" fill="black" font-size="4">3</text>
	
</svg>
EOT;
		$this->assertHTMLEquals($expected_html, $html);
	}


	/**
	 *
	 */
	public function test_is_show_legend(): void {
		$c = $this->getTestObject();

		$result = $c->isShowLegend();

		$this->assertTrue($result);
	}


	/**
	 *
	 */
	public function test_with_show_legend(): void {
		$c = $this->getTestObject();

		$result = $c->withShowLegend(false);

		$this->assertTrue(!$result->isShowLegend());
	}


	/**
	 *
	 */
	public function test_get_custom_total_value(): void {
		$c = $this->getTestObject();

		$c->withCustomTotalValue(1234);

		$result = $c->getCustomTotalValue();

		$this->assertEquals(1234, $result);
	}
}
