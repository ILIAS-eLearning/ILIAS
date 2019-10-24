<?php
/**
 * Base example showing how to plug a Multi-Select into a form
 */
function base()
{

    //Step 0: Declare dependencies
    global $DIC;

    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $ctrl = $DIC->ctrl();

    //Define the options.
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );

    //Step 1: define the select
    $multi = $ui->input()->field()->multiselect("Take your picks", $options, "This is the byline text")
        ->withRequired(true);

    //Step 2: define form and form actions
    $ctrl->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name_required',
        'multiselect'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, ['multi' => $multi]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name_required'] == "multiselect") {
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
