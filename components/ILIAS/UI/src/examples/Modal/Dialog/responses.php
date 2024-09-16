<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Dialog;

use ILIAS\UI\URLBuilder;

function responses()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //when expecting a response, we do not want to render other examples
    $example_namespace = ['dialog', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");

    $query_namespace = ['modal', 'example2'];
    list($url_builder, $token) = $url_builder->acquireParameters($query_namespace, "action");


    //build a modal:
    $uri = $url_builder->withParameter($token, "form")->buildURI();
    $modal = $factory->modal()->dialog($uri);

    //setup a response-wrapper
    $response = $factory->modal()->modalResponse();

    //fill the response according to (query-)paramters
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($token->getName())) {
        ob_clean();
        $action = $query->retrieve($token->getName(), $refinery->to()->string());

        $icon = $factory->symbol()->icon()->standard('someExample', 'Example');

        $out = [];

        if ($action === 'form') {
            $response = $response->withTitle('This is a Form in a Modal');

            $out[] = $factory->link()->bulky(
                $icon,
                'Switch Content',
                $url_builder->withParameter($token, "confirm")->buildURI()
            );
            $out[] = $factory->divider()->horizontal();

            //setup a form.
            $form = $factory->input()->container()->form()->standard(
                $uri->__toString(),
                [
                    $factory->input()->field()->text(
                        "Text Input",
                        "write 'close' to close the modal."
                    )->withRequired(true)
                ]
            );

            //evaluate result and modify response
            $result = '';
            $request = $DIC->http()->request();
            if ($request->getMethod() === 'POST') {
                $form = $form->withRequest($request);
                $result = $form->getData();
                if ($result) {
                    $result = array_shift($result);
                    $out[] = $factory->messageBox()->info('Your input: ' . $result);
                }
            }
            $out[] = $form;

            //attach the closing-command
            if ($result === 'close') {
                $response = $response->withCloseModal(true);
            }
        }
        if ($action === 'confirm') {
            $response = $response->withTitle('This is a Modal with a Descriptive Listing');

            $out[] = $factory->link()->bulky(
                $icon,
                'Switch Content to Form',
                $url_builder->withParameter($token, "form")->buildURI()
            );
            $out[] = $factory->divider()->horizontal();

            $out[] = $factory->messageBox()->confirmation('This is a confirmation of some sort concerning the items below:');

            $out[] = $factory->listing()->descriptive([
                "Title 1" => "Description 1",
                "Title 2" => "Description 2",
                "Title 3" => "Description 3"
            ]);

            $form = $factory->input()->container()->form()->withoutButtons(
                $url_builder->withParameter($token, "confirm")->buildURI()->__toString(),
                [
                    $factory->input()->field()->hidden()
                ]
            );

            $out[] = $form;

            //use buttons in modal:
            $response = $response->withButtons(
                $factory->button()->primary('Confirm', $form->getSubmitSignal()),
                $response->getCloseButton()
            );

            $request = $DIC->http()->request();
            if ($request->getMethod() === 'POST') {
                $response = $response->withCloseModal(true);
            }
        }

        $response = $response->withContent(...$out);
        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Dialog with Forms and Links', $modal->getShowSignal());
        return $renderer->render([$modal, $show_button]);
    }
}
