<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\DialogResponse;

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

    //when expecting a response, we do not want to render other examples
    $example_namespace = ['dialog', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");

    //The messagebox we are going to wrap into the dialog
    $message = $factory->messageBox()->success('some message box');

    //build the dialog
    $dialog = $factory->modal()->dialog($url_builder->buildURI());
    $show_button = $factory->button()->standard('Show Simple Dialog', $dialog->getShowSignal());


    //build the endpoint returning the wrapped message
    $response = $factory->modal()->dialogResponse($message);

    if($DIC->http()->wrapper()->query()->has($endpointtoken->getName())) {
        echo($renderer->renderAsync($response));
        exit();
    } else {
        return $renderer->render([
           $show_button,
           $dialog,
           $factory->divider()->horizontal(),
           $response
        ]);
    }

}
