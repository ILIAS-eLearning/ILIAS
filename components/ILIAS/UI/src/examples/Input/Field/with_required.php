<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field;

/**
 * Example showing the use of the withRequired() method
 */
function with_required()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: define the text field and make it a required field,
    // i.e. checking for its default requirement constraint
    // (for text fields: value must have a minimum length of 1)
    $text_input = $ui->input()->field()->text("Enter a name", "And make it a good one!");
    $text_input = $text_input->withRequired(true);

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $text_input]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
