<?php

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

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\Bar\Vertical;

/**
 * ---
 * expected output: >
 *   ILIAS shows a column-chart with an maximum x-value of 80 and four entries
 *   with values 80, 0, 18 and 55.
 *   Each entry is a vertical column with a height according to its value.
 * ---
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Generating Dimensions
    $c_dimension = $df->dimension()->cardinal();

    //Generating Dataset with points
    $dataset = $df->dataset(["Dataset" => $c_dimension]);

    $dataset = $dataset->withPoint("Item 1", ["Dataset" => 80]);
    $dataset = $dataset->withPoint("Item 2", ["Dataset" => 0]);
    $dataset = $dataset->withPoint("Item 3", ["Dataset" => 18]);
    $dataset = $dataset->withPoint("Item 4", ["Dataset" => 55]);

    //Generating and rendering the vertical chart
    $bar_chart = $f->chart()->bar()->vertical(
        "A vertical bar chart",
        $dataset
    );

    // render
    return $renderer->render($bar_chart);
}
