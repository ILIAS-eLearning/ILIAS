<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Filter\Standard;

/**
 * Example show how to create and render a basic filter.
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
            "multi_select" => $multi_select
        ],
        [true, true, true, false, true, true],
        true,
        true
    );

    //Step 3: Get filter data
    $filter_data = $DIC->uiService()->filter()->getData($filter);

    //Step 4: Render the filter
    return $renderer->render($filter) . "Filter Data: " . print_r($filter_data, true);
}
