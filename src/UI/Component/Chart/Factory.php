<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\UI\Component\Chart;

/**
 * This is how a factory for charts looks like.
 */
interface Factory
{
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
     * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
     * @return  \ILIAS\UI\Component\Chart\ScaleBar
     */
    public function scaleBar(array $items) : ScaleBar;


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
     * context:
     *     - Progress Meters are used inside courses on the content view.
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
    public function progressMeter() : ProgressMeter\Factory;


    /**
     * ---
     * description:
     *   purpose: >
     *      Bar Charts presents categorical data with rectangular bars at heights or lengths
     *      proportional to the values they represent. They are usually used to make
     *      comparisons between different categories or items.
     *   composition: >
     *      Bar Charts are composed of a title, a legend and the chart itself. The title
     *      and the legend can be hidden. One bar within the chart draws a
     *      measurement-item-value-pair. The composition of the axes in a Bar Chart depends
     *      on whether it is a Vertical Bar Chart or Horizontal Bar Chart. The legend
     *      comprises the label and color of each key. The keys represent the
     *      dimensions of the bars displayed.
     *   effect: >
     *      Hovering over a bar within the Bar Chart triggers a tooltip displaying its
     *      measurement-item-value-pair and its dimension.
     *      Clicking on a key in the legend hides the bars of that dimension.
     *      Clicking again on it will show the respective bars.
     * context:
     *     - Bar Charts are to be used to visualize competence records.
     * rules:
     *   composition:
     *     1: >
     *        Especially when multiple dimensions are used, Bar Charts SHOULD
     *        display a legend, which shows the label and color of the keys.
     *   style:
     *     1: >
     *        Bars of different dimensions SHOULD have different colors to be
     *        distinguishable.
     *   responsiveness:
     *     1: >
     *        On smaller screens, the Bar Chart MUST shrink until it reaches its minimum size.
     *   accessibility:
     *     1: >
     *        For each dimension, the measurement-item-value-pairs MUST be presented
     *        in a textual form, which MUST be accessible for screen readers.
     * ---
     * @return \ILIAS\UI\Component\Chart\Bar\Factory
     */
    public function bar() : \ILIAS\UI\Component\Chart\Bar\Factory;
}
