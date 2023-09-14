<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\DialogResponse;

use ILIAS\UI\URLBuilder;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $query = $DIC->http()->wrapper()->query();

    /**
     * build a Modal Response with Components implementing ModalContent
     */
    $out = [];
    $out[] = $ui_factory->messageBox()->confirmation('This is a confirmation of some sort concerning the items below:');
    $out[] = $ui_factory->divider()->horizontal();
    $out[] = $ui_factory->listing()->descriptive([
        "Title 1" => "Description 1",
        "Title 2" => "Description 2",
        "Title 3" => "Description 3"
    ]);

    $response = $ui_factory->modal()->dialogResponse(
        'This is the title of the Modal',
        ...$out
    );


    //we will use a Modal, too, to show the Repsonse:
    $example_namespace = ['dialog', 'endpoints'];
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");
    $uri = $url_builder->buildURI();

    $modal = $ui_factory->modal()->dialog($uri);

    //render response
    $out = [$response];

    //render Button and Modal, if not requested by Modal itself
    if (!$query->has($endpointtoken->getName())) {
        $out[] = $ui_factory->divider()->horizontal();
        $out[] = $modal;
        $out[] = $ui_factory->button()->standard('Show Response in Dialog', $modal->getShowSignal());
    }

    return $renderer->render($out);
}
