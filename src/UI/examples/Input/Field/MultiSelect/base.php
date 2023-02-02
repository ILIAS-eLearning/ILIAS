<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\MultiSelect;

/**
 * Base example showing how to plug a Multi-Select into a form
 */
function base()
{
    //declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //define options.
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );

    //define the select
    $multi = $ui->input()->field()->multiselect("Take your picks", $options, "This is the byline text")
        ->withRequired(true);

    //define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['multi' => $multi]);


    //implement some form data processing.
    if ($request->getMethod() == "POST") {
        try {
            $form = $form->withRequest($request);
            $result = $form->getData();
        } catch (\InvalidArgumentException $e) {
            $result = "No result. Probably, the other form was used.";
        }
    } else {
        $result = "No result yet.";
    }

    //render the select with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
