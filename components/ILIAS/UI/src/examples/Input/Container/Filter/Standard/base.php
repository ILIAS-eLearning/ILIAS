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

namespace ILIAS\UI\examples\Input\Container\Filter\Standard;

/**
 * ---
 * description: >
 *   Example shows how to create and render a basic filter.
 *
 * expected output: >
 *   If the filter with the toggle button on the right top is disabled "Filter Data" is empty.
 *   If the filter with the toggle button on the right top is enabled "Filter Data" includes the data entered into the filter.
 *   If the filter is minimized all inputs will be hidden and the values will be displayed minimized.
 *   If the filter inputs will be applied by clicking the button "Apply" the filter will be employed automatically. The data
 *   will be displayed accordingly.
 *   Clicking "Reset" will reset all filter.
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define some input fields to plug into the filter.
    $title_input = $ui->input()->field()->text("Title");
    $select = $ui->input()->field()->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]);
    $with_def = $ui->input()->field()->text("With Default")->withValue("Def.Value");
    $init_hide = $ui->input()->field()->text("Hidden initially");
    $number = $ui->input()->field()->numeric("Number");
    $multi_select = $ui->input()->field()->multiSelect(
        "Multi Selection",
        ["one" => "Num One", "two" => "Num Two", "three" => "Num Three", "four" => "Num Four", "five" => "Num Five"]
    );
    $date = $ui->input()->field()->dateTime("Date/Time")->withUseTime(true);
    $duration = $ui->input()->field()->duration("Duration")->withUseTime(true);

    //Step 2: Define the filter and attach the inputs.
    $action = $DIC->ctrl()->getLinkTargetByClass("ilsystemstyledocumentationgui", "entries", "", true);
    $filter = $DIC->uiService()->filter()->standard(
        "filter_ID",
        $action,
        [
            "title" => $title_input,
            "select" => $select,
            "with_def" => $with_def,
            "init_hide" => $init_hide,
            "number" => $number,
            "multi_select" => $multi_select,
            "date" => $date,
            "duration" => $duration
        ],
        [true, true, true, false, true, true, true, true],
        true,
        true
    );

    //Step 3: Get filter data
    $filter_data = $DIC->uiService()->filter()->getData($filter);

    //Step 4: Render the filter
    return $renderer->render($filter) . "Filter Data: " . "<pre>" . print_r($filter_data, true) . "</pre><br/>";
}
