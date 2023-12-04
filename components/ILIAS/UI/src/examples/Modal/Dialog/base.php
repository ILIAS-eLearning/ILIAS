<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Dialog;

use ILIAS\UI\Component\Modal\DialogContent;

use ILIAS\UI\URLBuilder;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //The messagebox we are going to wrap into the dialog
    $message = $factory->messageBox()->success('some message box');

    //when expecting a response, we do not want to render other examples
    $example_namespace = ['dialog', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");

    //build the dialog
    $query_namespace = ['dialog', 'example0'];
    list($url_builder, $token) = $url_builder->acquireParameters($query_namespace, "show");
    $url_builder = $url_builder->withParameter($token, "true");
    $dialog = $factory->modal()->dialog($url_builder->buildURI());

    //build the endpoint returning the wrapped message
    $query = $DIC->http()->wrapper()->query();
    if($query->has($token->getName())) {
        $response = $factory->modal()->dialogResponse($message);
        echo($renderer->renderAsync($response));
        exit();
    }

    //a button to open the dialog:
    $show_button = $factory->button()->standard('Show Simple Dialog', $dialog->getShowSignal());

    if (!$query->has($endpointtoken->getName())) {
        return $renderer->render([
           $message,
           $dialog,
           $show_button
        ]);
    }
}
