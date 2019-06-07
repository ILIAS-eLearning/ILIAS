<?php

namespace ILIAS\UI\Implementation\Component\Chart\PieChart;

use ILIAS\UI\Component\Chart\PieChart\PieChart as PieChartInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Chart\PieChart
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritDoc
	 */
	protected function getComponentInterfaceName(): array {
		return [ PieChart::class ];
	}


	/**
	 * @inheritDoc
	 */
	public function render(Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		return $this->renderStandard($component, $default_renderer);
	}


	/**
	 * @param PieChartInterface $component
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderStandard(PieChartInterface $component, RendererInterface $default_renderer): string {
		$tpl = $this->getTemplate("tpl.piechart.html", true, true);

		foreach ($component->getSections() as $section) {
			$tpl->setCurrentBlock("section");
			$tpl->setVariable("STROKE_LENGTH", $section->getStrokeLength());
			$tpl->setVariable("OFFSET", $section->getOffset());
			$tpl->setVariable("SECTION_COLOR", $section->getColor()->asHex());
			$tpl->parseCurrentBlock();
		}

		if ($component->isShowLegend()) {
			foreach ($component->getSections() as $section) {
				$tpl->setCurrentBlock("legend");
				$tpl->setVariable("SECTION_COLOR", $section->getColor()->asHex());
				$tpl->setVariable("LEGEND_Y_PERCENTAGE", $section->getLegendEntry()->getYPercentage());
				$tpl->setVariable("LEGEND_TEXT_Y_PERCENTAGE", $section->getLegendEntry()->getTextYPercentage());
				$tpl->setVariable("LEGEND_FONT_SIZE", $section->getLegendEntry()->getTextSize());
				$tpl->setVariable("RECT_SIZE", $section->getLegendEntry()->getSquareSize());

				if ($component->isValuesInLegend()) {
					$section_name = sprintf($section->getName() . " (%s)", $section->getValue()->getValue());
				} else {
					$section_name = $section->getName();
				}

				$tpl->setVariable("SECTION_NAME", $section_name);
				$tpl->parseCurrentBlock();
			}
		}

		foreach ($component->getSections() as $section) {
			$tpl->setCurrentBlock("section_text");
			$tpl->setVariable("VALUE_X_PERCENTAGE", $section->getValue()->getXPercentage());
			$tpl->setVariable("VALUE_Y_PERCENTAGE", $section->getValue()->getYPercentage());
			$tpl->setVariable("SECTION_VALUE", round($section->getValue()->getValue(), 2));
			$tpl->setVariable("VALUE_FONT_SIZE", $section->getValue()->getTextSize());
			$tpl->setVariable("TEXT_COLOR", $section->getTextColor()->asHex());
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("total");
		$total_value = $component->getCustomTotalValue();
		if (is_null($total_value)) {
			$total_value = $component->getTotalValue();
		}
		$tpl->setVariable("TOTAL_VALUE", round($total_value, 2));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}
