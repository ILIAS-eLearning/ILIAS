<?php
/**
 * Base example showing how to plug a Select into a form
 */
function with_required()
{

    //Step 0: Declare dependencies
    global $DIC;

    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $ctrl = $DIC->ctrl();

    //Define the options.
    $options = array(
        "1" => "Type 1",
        "2" => "Type 2",
        "3" => "Type 3",
        "4" => "Type 4",
    );

    //Step 1: define the select
    $select = $ui->input()->field()->select("Choose an Option", $options, "This is the byline text")->withRequired(true);

    //Step 2: define form and form actions
    $ctrl->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name_required',
        'select'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [$select]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name_required'] == "select") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the select with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
