<?php

namespace ILIAS\UI\Implementation\Component\Chart\PieChart;

use ILIAS\UI\Component\Chart\PieChart\SectionValue as SectionValueInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class SectionValue
 *
 * @package ILIAS\UI\Implementation\Component\Chart\PieChart
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SectionValue implements SectionValueInterface {

	use ComponentHelper;
	/**
	 * @var float
	 */
	private $value;
	/**
	 * @var float
	 */
	private $x_percentage;
	/**
	 * @var float
	 */
	private $y_percentage;
	/**
	 * @var int
	 */
	private $text_size;


	/**
	 * SectionValue constructor
	 *
	 * @param float $value
	 * @param float $stroke_dasharray
	 * @param float $stroke_dashoffset
	 * @param float $section_percentage
	 */
	public function __construct(float $value, float $stroke_dasharray, float $stroke_dashoffset, float $section_percentage) {
		$this->checkFloatArg("value", $value);
		$this->value = $value;
		$this->checkFloatArg("stroke_dasharray", $stroke_dasharray);
		$this->checkFloatArg("stroke_dashoffset", $stroke_dashoffset);
		$this->checkFloatArg("section_percentage", $section_percentage);
		$this->calcChartCoords($stroke_dasharray, $stroke_dashoffset);
		$this->calcTextSize($section_percentage);
	}


	/**
	 * @param float $stroke_dasharray
	 * @param float $stroke_dashoffset
	 */
	private function calcChartCoords(float $stroke_dasharray, float $stroke_dashoffset): void {
		$angle_dasharray = abs($stroke_dasharray) * 3.6 * 2.549;
		$angle_dashoffset = abs($stroke_dashoffset) * 3.6 * 2.549;
		$final_angle_rad = deg2rad(360 - ($angle_dashoffset + $angle_dasharray / 2));

		$this->x_percentage = (0.25 + (cos($final_angle_rad) * 0.135)) * 100;
		$this->y_percentage = (0.5 - (sin($final_angle_rad) * 0.275)) * 100;
	}


	/**
	 * @param float $section_percentage
	 */
	private function calcTextSize(float $section_percentage): void {
		if ($section_percentage <= 7) {
			$this->text_size = 0;
		} else {
			$this->text_size = 3;
		}
	}


	/**
	 * @inheritDoc
	 */
	public function getValue(): float {
		return $this->value;
	}


	/**
	 * @inheritDoc
	 */
	public function getXPercentage(): float {
		return $this->x_percentage;
	}


	/**
	 * @inheritDoc
	 */
	public function getYPercentage(): float {
		return $this->y_percentage;
	}


	/**
	 * @inheritDoc
	 */
	public function getTextSize(): int {
		return $this->text_size;
	}
}
