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
use ILIAS\UI\Component\Chart\Bar\GroupConfig;
use ILIAS\UI\Component\Chart\Bar\YAxis;

/**
 * ---
 * expected output: >
 *   ILIAS shows a base column-chart but with stacked bars in different colors.
 * ---
 */
function partly_stacked()
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
        "Dataset 1.1" => $c_dimension,
        "Dataset 1.2" => $c_dimension,
        "Dataset 2" => $c_dimension,
    ], [
        "Grouped Dataset 1" => $df->dimension()->group("Dataset 1.1", "Dataset 1.2")
    ]);

    $dataset = $dataset->withPoint(
        "Item 1",
        [
            "Dataset 1.1" => 8,
            "Dataset 1.2" => 7,
            "Dataset 2" => 10
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 2",
        [
            "Dataset 1.1" => 2,
            "Dataset 1.2" => 5,
            "Dataset 2" => 3
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 3",
        [
            "Dataset 1.1" => -4,
            "Dataset 1.2" => 10,
            "Dataset 2" => 6
        ]
    );

    //Generating Bar Configurations
    $b1 = new BarConfig();
    $b1 = $b1->withColor($df->color("#12436D"));
    $b2 = new BarConfig();
    $b2 = $b2->withColor($df->color("#28A197"));
    $b3 = new BarConfig();
    $b3 = $b3->withColor($df->color("#801650"));
    $g1 = new GroupConfig();
    $g1 = $g1->withStacked(true);

    $bars = [
        "Dataset 1.1" => $b1,
        "Dataset 1.2" => $b2,
        "Dataset 2" => $b3
    ];
    $groups = [
        "Grouped Dataset 1" => $g1
    ];

    //Generating and rendering the vertical chart
    $bar = $f->chart()->bar()->vertical(
        "A vertical partly stacked bar chart",
        $dataset,
        $bars,
        $groups
    );

    // render
    return $renderer->render($bar);
}
