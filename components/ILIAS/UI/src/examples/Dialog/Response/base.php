<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dialog\Response;

use ILIAS\UI\Component\Dialog\DialogContent;
use ILIAS\UI\URLBuilder;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //a response may contain Components implementing DialogContent interface.
    $content = $factory->input()->container()->form()->standard(
        $url_builder->buildURI()->__toString(),
        [$factory->input()->field()->text("Text Input")]
    );

    $response = $factory->dialog()->response($content);

    //endpoint to return response on (asynch) call
    $refinery = $DIC['refinery'];
    $example_namespace = ['dialog', 'response'];
    list($url_builder, $url_token) = $url_builder->acquireParameters(
        $example_namespace,
        "response"
    );
    $query = $DIC->http()->wrapper()->query();
    if($query->has($url_token->getName())) {
        echo($renderer->renderAsync($response));
        exit();
    }

    //build the dialog
    $dialog = $factory->dialog()->standard($url_builder->buildURI());
    $show_button = $factory->button()->standard('Show Dialog', $dialog->getShowSignal());


    //show the response contents:
    $txt_response = $factory->legacy(
        '<pre>'
        . htmlentities($renderer->render($response))
        . '</pre>'
    );

    return $renderer->render([
       $txt_response,
       $show_button,
       $dialog
    ]);

}
