<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Url;

/**
 * This example shows how to create and render a basic URL input field with an value
 * attached to it. It does also contain data processing.
 */
function with_value()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the URL input field and attach some default value
    $url_input = $ui->input()->field()->url("Basic Input", "Just some basic input with 
    some default url value.")
        ->withValue("https://www.ilias.de/");

    //Step 2: Define the form and attach the section
    $form = $ui->input()->container()->form()->standard("#", [$url_input]);

    //Step 3: Define some data processing
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData()[0] ?? "";
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the form with the URL input field
    return
        "<pre>" . print_r($result, true) . "</pre><br />" .
        $renderer->render($form);
}
