<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Tag;

/**
 * Example show how to create and render a basic tag input field and attach it to a
 * form. This example does not contain any data processing.
 */
function base_with_data()
{
    // Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: Define the tag input field
    $tag_input = $ui->input()->field()->tag(
        "Basic TagInput",
        ['Interesting & fascinating', 'Boring, dull', 'Animating', 'Repetitious'],
        "Just some tags"
    );

    // Step 2, define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['f2' => $tag_input]);

    // Step 3, implement some form data processing.
    if ($request->getMethod() === "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    // Step 4, return the rendered form with data
    return "<pre>"
        . print_r($result, true)
        . "</pre><br/>"
        . $renderer->render($form);
}
