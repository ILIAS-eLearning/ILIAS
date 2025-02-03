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

use ILIAS\UI\Component\Chart\Bar\Bar;
use ILIAS\UI\Component\Chart\Bar\BarConfig;
use ILIAS\UI\Component\Chart\Bar\YAxis;

/**
 * ---
 * expected output: >
 *   ILIAS shows a base column-chart but customized. The left is labeled with three coloured rectanlges and captions.
 *   The y-bar is sectioned in three parts, each part consisting of three columns,
 *   one for each dataset (set 2 of Item 2 has a value of 0, thus not showing a bar).
 * ---
 */
function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Generating Dimensions
    $c_dimension = $df->dimension()->cardinal();

    //Generating Dataset with points and tooltips
    $dataset = $df->dataset([
       "Dataset 1" => $c_dimension,
        "Dataset 2" => $c_dimension,
        "Dataset 3" => $c_dimension,
    ]);

    $dataset = $dataset->withPoint(
        "Item 1",
        [
            "Dataset 1" => 75,
            "Dataset 2" => 80,
            "Dataset 3" => 100
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 2",
        [
            "Dataset 1" => 45,
            "Dataset 2" => 30,
            "Dataset 3" => 90
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 3",
        [
            "Dataset 1" => 50,
            "Dataset 2" => 100,
            "Dataset 3" => 65.5
        ]
    );

    //Generating Bar Configurations
    $b1 = new BarConfig();
    $b1 = $b1->withColor($df->color("#12436D"));
    $b2 = new BarConfig();
    $b2 = $b2->withColor($df->color("#28A197"));
    $b3 = new BarConfig();
    $b3 = $b3->withColor($df->color("#801650"));

    $bars = [
        "Dataset 1" => $b1,
        "Dataset 2" => $b2,
        "Dataset 3" => $b3
    ];

    //Generating and rendering the vertical chart
    $bar = $f->chart()->bar()->vertical(
        "A vertical bar chart",
        $dataset,
        $bars
    );
    $bar = $bar->withTitleVisible(false);
    $bar = $bar->withLegendPosition("left");
    $y_axis = new YAxis();
    $y_axis = $y_axis->withPosition("right");
    $y_axis = $y_axis->withStepSize(10);
    $y_axis = $y_axis->withBeginAtZero(false);
    $bar = $bar->withCustomYAxis($y_axis);

    // render
    return $renderer->render($bar);
}
