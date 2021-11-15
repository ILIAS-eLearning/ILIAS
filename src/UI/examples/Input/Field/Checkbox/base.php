<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Checkbox;

/**
 * Base example showing how to plug a checkbox into a form
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: define the checkbox, and turning it on
    $checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Check or not.")
            ->withValue(true);

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $checkbox_input]);

    //Step 3: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
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
