<?php
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
    ],
        [true, true, true, false],
        true,
        true
    );

    //Step 3: Get filter data
    // @Todo: This needs to be improved. This appproach is copied from initFilter in class.ilContainerGUI.php .
    // it fixes the broken example for the moment. This seems necessary atm since text inputs will handle empty inputs
    // as null an throw an InvalidArgumentException. Also see comment in withInput of Input Fields and related bug:
    // https://mantis.ilias.de/view.php?id=27909 . An other approach is performed in ilPluginsOverviewTableFilterGUI
    // Where the exception is catched and an empty array returned.
    $filter_data = [];

    if ($DIC->http()->request()->getMethod() == "POST") {
        $filter_data = $DIC->uiService()->filter()->getData($filter);
    } else {
        foreach ($filter->getInputs() as $k => $i) {
            $filter_data[$k] = $i->getValue();
        }
    }

    //Step 4: Render the filter
    return $renderer->render($filter) . "Filter Data: " . print_r($filter_data, true);
}
