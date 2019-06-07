<?php

namespace ILIAS\UI\Component\Chart;

use ILIAS\UI\Component\Chart\PieChart\PieChart;
use ILIAS\UI\Component\Chart\PieChart\PieChartItem as PieChartItemInterface;

/**
 * This is how a factory for glyphs looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Scale Bars are used to display a set of items some of which
	 *     especially highlighted. E.g. they can be used to inform about a
	 *     score or target on a rank ordered scale.
	 *   composition: >
	 *     Scale Bars are composed of of a set of bars of equal size. Each
	 *     bar contains a title. The highlighted elements
	 *     differ from the others through their darkened background.
	 *
	 * context:
	 *   - Scale Bars are are used in the Competence Management on the Personal Desktop.
	 *
	 * rules:
	 *   composition:
	 *     1: Each Bar of the Scale Bars MUST bear a title.
	 *     2: The title of Scale Bars MUST NOT contain any other content than text.
	 * ----
	 *
	 * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
	 *
	 * @return  \ILIAS\UI\Component\Chart\ScaleBar
	 */
	public function scaleBar(array $items);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Progress Meters are used to display a progress or performance.
	 *     E.g. they can be used to inform about a progress in a learning objective or to
	 *     compare the performance between the initial and final test in a course.
	 *   composition: >
	 *     Progress Meters are composed of one or two bars inside a horseshoe-like container.
	 *     The bars change between two colors, to identify a specific reached value. It
	 *     additionally may show a percentage of the values and also an identifying text.
	 *
	 * rules:
	 *   composition:
	 *     1: Progress Meters MUST contain a maximum value. It MUST be numeric and represents the maximum value.
	 *     2: Progress Meters MUST contain a main value. It MUST be a numeric value between 0 and the maximum. It is represented as the main bar.
	 *     3: Progress Meters SHOULD contain a required value. It MUST be a numeric value between 0 and the maximum. It represents the required value that has to be reached.
	 *
	 * ---
	 * @return \ILIAS\UI\Component\Chart\ProgressMeter\Factory
	 */
	public function progressMeter();


	/**
	 * ---
	 * description:
	 *   purpose: Pie Charts are used to display data values in an appealing and easy-to-understand way to the user.
	 *   composition:
	 *     Pie Charts are composed of one big circle which is divided into various sections, each section represents a title with a value
	 *     in a specific color.
	 *     There is also a legend on the right hand side of the circle showing which color corresponds to which title.
	 *   effect: Pie Charts convey information, they are not interactive.
	 *   rivals:
	 *     Rival 1:
	 *       Progress Meter - Progress Meters can also be used to compare values in a circular shape. The pie chart allows there to be more values
	 *       within a single chart.
	 *
	 * rules:
	 *   usage:
	 *     1: Pie Charts MUST contain at least 1 Pie Chart Item (PieChartItem).
	 *     2: Pie Charts MUST contain 12 or less Pie Chart Items (PieChartItem).
	 *     3: Pie Charts MUST contain Pie Chart Items with titles containing 35 or less characters.
	 *     4: Pie Charts Items MUST contain a title.
	 *     5: Pie Charts Items MUST contain a float value.
	 *     6: Pie Charts Items MUST contain a color for the section.
	 *   responsiveness:
	 *     7: This element will automatically adjust its size with the outer container. Note that this is a SVG element and works well with scaling.
	 *
	 * ---
	 * @param PieChartItemInterface[] $pieChartItems
	 *
	 * @return PieChart
	 */
	public function pieChart(array $pieChartItems): PieChart;
}
