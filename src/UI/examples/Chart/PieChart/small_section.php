<?php

use ILIAS\Data\Color;
use ILIAS\UI\Implementation\Component\Chart\PieChart\PieChartItem;

/**
 * @return string
 */
function small_section(): string {
	global $DIC;

	$c = $DIC->ui()->factory()->chart()->pieChart([
		new PieChartItem("One", 0.1, new Color(20, 220, 20)),
		new PieChartItem("Two", 0.4, new Color(220, 20, 220)),
		new PieChartItem("Three", 0.8, new Color(20, 220, 220)),
		new PieChartItem("Four", 1.2, new Color(220, 220, 20)),
		new PieChartItem("Five", 7.2, new Color(0, 0, 0), new Color(255, 255, 255)),
		new PieChartItem("Six", 4, new Color(100, 100, 100), new Color(255, 255, 0))
	]);

	return $DIC->ui()->renderer()->render($c);
}
