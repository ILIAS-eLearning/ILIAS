<?php

namespace ILIAS\UI\Implementation\Component\Chart\PieChart;

use ILIAS\Data\Color;
use ILIAS\UI\Component\Chart\PieChart\LegendEntry as LegendEntryInterface;
use ILIAS\UI\Component\Chart\PieChart\PieChartItem as PieChartItemInterface;
use ILIAS\UI\Component\Chart\PieChart\Section as SectionInterface;
use ILIAS\UI\Component\Chart\PieChart\SectionValue as SectionValueInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Section
 *
 * @package ILIAS\UI\Implementation\Component\Chart\PieChart
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Section implements SectionInterface {

	use ComponentHelper;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var SectionValueInterface
	 */
	protected $value;
	/**
	 * @var float
	 */
	protected $percentage;
	/**
	 * @var float
	 */
	protected $stroke_length;
	/**
	 * @var float
	 */
	protected $offset;
	/**
	 * @var Color
	 */
	protected $color;
	/**
	 * @var LegendEntryInterface
	 */
	protected $legend;
	/**
	 * @var Color
	 */
	protected $textColor;


	/**
	 * Section constructor
	 *
	 * @param PieChartItemInterface $item
	 * @param float                 $totalValue
	 * @param int                   $numSections
	 * @param int                   $index
	 * @param float                 $offset
	 */
	public function __construct(PieChartItemInterface $item, float $totalValue, int $numSections, int $index, float $offset) {
		$name = $item->getName();
		$value = $item->getValue();
		$color = $item->getColor();
		$textColor = $item->getTextColor();
		$this->checkStringArg("name", $name);
		$this->name = $name;
		$this->checkFloatArg("value", $value);
		$this->checkArgInstanceOf("color", $color, Color::class);
		$this->color = $color;
		$this->checkArgInstanceOf("textColor", $textColor, Color::class);
		$this->textColor = $textColor;
		$this->checkFloatArg("totalValue", $totalValue);
		$this->checkIntArg("numSections", $numSections);
		$this->checkIntArg("index", $index);
		$this->checkFloatArg("offset", $offset);
		$this->offset = $offset;

		$this->calcPercentage($totalValue, $value);
		$this->calcStrokeLength();

		$this->legend = new LegendEntry($this->name, $numSections, $index);
		$this->value = new SectionValue($value, $this->stroke_length, $this->offset, $this->percentage);
	}


	/**
	 * @param float $totalValue
	 * @param float $sectionValue
	 */
	private function calcPercentage(float $totalValue, float $sectionValue): void {
		$this->percentage = $sectionValue / $totalValue * 100;
	}


	/**
	 *
	 */
	private function calcStrokeLength(): void {
		$this->stroke_length = $this->percentage / 2.549;
	}


	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->name;
	}


	/**
	 * @inheritDoc
	 */
	public function getValue(): SectionValueInterface {
		return $this->value;
	}


	/**
	 * @inheritDoc
	 */
	public function getPercentage(): float {
		return $this->percentage;
	}


	/**
	 * @inheritDoc
	 */
	public function getStrokeLength(): float {
		return $this->stroke_length;
	}


	/**
	 * @inheritDoc
	 */
	public function getOffset(): float {
		return $this->offset;
	}


	/**
	 * @inheritDoc
	 */
	public function getColor(): Color {
		return $this->color;
	}


	/**
	 * @inheritDoc
	 */
	public function getLegendEntry(): LegendEntryInterface {
		return $this->legend;
	}


	/**
	 * @return Color
	 */
	public function getTextColor(): Color {
		return $this->textColor;
	}


	/**
	 * @inheritDoc
	 */
	public function withTextColor(Color $textColor): SectionInterface {
		$clone = clone $this;
		$clone->textColor = $textColor;

		return $clone;
	}
}
