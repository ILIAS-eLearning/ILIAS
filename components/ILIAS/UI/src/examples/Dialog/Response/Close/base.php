<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dialog\Response\Close;

use ILIAS\UI\Component\Dialog\DialogContent;
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
    $example_namespace = ['dialog', 'close'];
    list($url_builder, $action_token) = $url_builder->acquireParameters(
        $example_namespace,
        "action"
    );

    //the endpoint; act according to parameter
    $query = $DIC->http()->wrapper()->query();
    if($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string());
        if($action === 'closecommand') {
            //a response simply closing the modal
            $response = $factory->dialog()->close();
        } else {
            //The messagebox we are going to wrap into the dialog
            $close = $factory->link()->standard(
                'send close command',
                $url_builder->withParameter($action_token, 'closecommand')->buildURI()->__toString()
            );

            $message = $factory->messageBox()->info('some message box')
                ->withLinks([$close]);
            $response = $factory->dialog()->response($message);
        }
        echo($renderer->renderAsync($response));
        exit();
    }

    //render dialog and button
    $dialog = $factory->dialog()->standard($url_builder->buildURI());
    return $renderer->render([
        $factory->button()->standard('Show Dialog', $dialog->getShowSignal()),
        $dialog
    ]);

}
