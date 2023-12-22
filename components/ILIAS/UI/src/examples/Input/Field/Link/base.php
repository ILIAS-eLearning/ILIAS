<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Link;

function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $link_input = $ui->input()->field()->link("Link Input", "Enter a label and the url ")
        ->withValue(['ILIAS Homepage', "https://www.ilias.de/"]);

    $form = $ui->input()->container()->form()->standard("#", [$link_input]);

    $result = "No result yet.";
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $data = $form->getData();
        if($data) {
            $result = $data[0];
        }
    }

    return
        "<pre>" . print_r($result, true) . "</pre><br />" .
        $renderer->render($form);
}
