<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Radio;

/**
 * Example showing how to plug a disabled radio into a form
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();


    //Step 1: define the radio
    $radio = $ui->input()->field()->radio("Radio", "Cannot check an option")
        ->withOption('value1', 'label1')
        ->withOption('value2', 'label2')
        ->withOption('value3', 'label3')
        ->withDisabled(true);


    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 3: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked and null if unchecked.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the radio with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
