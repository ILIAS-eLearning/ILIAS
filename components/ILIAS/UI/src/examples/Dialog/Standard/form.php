<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dialog\Standard;

use ILIAS\UI\URLBuilder;

function form()
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

    //build the dialog
    $query_namespace = ['dialog', 'example2'];
    list($url_builder, $action_token) = $url_builder->acquireParameters($query_namespace, "action");
    $url_builder = $url_builder->withParameter($action_token, "form");
    $dialog = $factory->dialog()->standard($url_builder->buildURI());

    //fill the response according to (query-)paramters
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_token->getName())
        && $query->has($action_token->getName())
        && $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string()) === 'form'
    ) {

        //setup a form.
        $uri = $url_builder->buildURI()->__toString();
        $form = $factory->input()->container()->form()->standard(
            $uri,
            [
                $factory->input()->field()->text(
                    "Text Input",
                    "write 'close' to close the dialog."
                )->withRequired(true)
            ]
        );

        //set response
        $response = $factory->dialog()->response($form);

        $request = $DIC->http()->request();
        if ($request->getMethod() === 'POST') {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if($data !== null && reset($data) === 'close') {
                /**
                 * alternatively:
                 * $response = $response->withCloseModal(true);
                 */
                $response = $factory->dialog()->close();
            } else {
                $response = $factory->dialog()->response($form);
            }
        }

        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Dialog with Form', $dialog->getShowSignal());
        return $renderer->render([$dialog, $show_button]);
    }
}
