<?php

namespace ILIAS\UI\Component\Chart\PieChart;

use ILIAS\UI\Component\Component;

/**
 * Interface PieChart
 *
 * @package ILIAS\UI\Component\Chart\PieChart
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface PieChart extends Component {

	const MAX_ITEMS = 12;
	const ERR_NO_ITEMS = "Empty array supplied as argument";
	const ERR_TOO_MANY_ITEMS = "More than " . self::MAX_ITEMS . " Pie Chart Items supplied";


	/**
	 * Get all the created sections. Note that sections are different from PieChartItems
	 *
	 * @return Section[]
	 */
	public function getSections(): array;


	/**
	 * Get the combined value of all sections that is shown in the center
	 *
	 * @return float
	 */
	public function getTotalValue(): float;


	/**
	 * Set a flag for the value of sections to show up in the legend next to the title
	 *
	 * @param bool $state
	 *
	 * @return self
	 */
	public function withValuesInLegend(bool $state): self;


	/**
	 * Get the flag that controls if the value of sections show up in the legend next to the title
	 *
	 * @return bool
	 */
	public function isValuesInLegend(): bool;


	/**
	 * @param bool $state
	 *
	 * @return self
	 */
	public function withShowLegend(bool $state): self;


	/**
	 * @return bool
	 */
	public function isShowLegend(): bool;


	/**
	 * @param float|null $custom_total_value
	 *
	 * @return self
	 */
	public function withCustomTotalValue(?float $custom_total_value = null): self;


	/**
	 * @return float|null
	 */
	public function getCustomTotalValue(): ?float;
}
