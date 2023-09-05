<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Dialog;

use ILIAS\UI\URLBuilder;

function parameters()
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


    //build a modal
    $query_namespace = ['modal', 'example1'];
    list($url_builder, $action_token, $amount_token) = $url_builder->acquireParameters($query_namespace, "action", "amount");
    $uri = $url_builder->withParameter($action_token, "showmodal")->buildURI();
    $uri = $url_builder->withParameter($amount_token, "1")->buildURI();
    $modal = $factory->modal()->dialog($uri);


    //build some endpoint
    $query = $DIC->http()->wrapper()->query();
    $icon = $factory->symbol()->icon()->standard('someExample', 'Example');
    $item = $icon;
    $out = [];

    if ($query->has($amount_token->getName())) {
        $amount = $query->retrieve($amount_token->getName(), $refinery->kindlyTo()->int()) ;
        foreach (range(1, $amount) as $idx) {
            $item = $factory->modal()->interruptiveItem()
                ->keyValue((string)$idx, 'item' . $idx, 'some value');
            $out[] = $item;
        }
    }


    //wrap answer in Modal Response
    $response = $factory->modal()->modalResponse(
        'This is a Simple Modal',
        ...$out
    );


    if ($query->has($action_token->getName())) {
        //add a close-button to the Modal
        $response = $response->withButtons($response->getCloseButton('close this'));
        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Simple Dialog', $modal->getShowSignal());

        $uri = $url_builder->withParameter($amount_token, "8")->buildURI();
        $show_button8 = $factory->button()->standard('Show Dialog with Parameter', $modal->getShowSignal($uri));

        $uri = $url_builder->withParameter($amount_token, "78")->buildURI();
        $show_button78 = $factory->button()->standard('Show Dialog with a lot of Items', $modal->getShowSignal($uri));

        return $renderer->render([
            $modal,
            $show_button, $show_button8, $show_button78
        ]);
    }
}
