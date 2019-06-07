<?php

namespace ILIAS\UI\Component\Chart\PieChart;

/**
 * Interface SectionValue
 *
 * @package ILIAS\UI\Component\Chart\PieChart
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface SectionValue {

	/**
	 * Get the actual value
	 *
	 * @return float
	 */
	public function getValue(): float;


	/**
	 * Get the x percentage this value will be displayed at. (On top of the pie chart section)
	 *
	 * @return float
	 */
	public function getXPercentage(): float;


	/**
	 * Get the x percentage this value will be displayed at. (On top of the pie chart section)
	 *
	 * @return float
	 */
	public function getYPercentage(): float;


	/**
	 * Get the size of the value text (On to pof the pie chart section)
	 *
	 * @return int
	 */
	public function getTextSize(): int;
}
