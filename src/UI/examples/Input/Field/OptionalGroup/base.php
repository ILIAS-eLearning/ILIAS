<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\OptionalGroup;

/**
 * Example showing how a dependant group (aka sub form) might be attached to a checkbox.
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the fields in the group
    $dependant_field = $ui->input()->field()->text("Item 1", "Just some dependent group field");
    $dependant_field2 = $ui->input()->field()->datetime("Item 2", "a dependent date");

    //Step 2: define the checkbox and attach the dependant group
    $checkbox_input = $ui->input()->field()->optionalGroup(
        [
            "dependant_text" => $dependant_field,
            "dependant_date" => $dependant_field2
        ],
        "Optional Group",
        "Check to display group field."
    );

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $checkbox_input]);

    //Step 4: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
