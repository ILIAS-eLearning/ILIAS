<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Prompt\Response\Redirect;

use ILIAS\UI\Component\Prompt\PromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example demonstrates how to use commands in the Response.
 *
 * expected output: >
 *   When clicking the button, the Prompt shows a Mesaae Box with a Link.
 *   Using the Link will redirect the page to the ILIAS homepage.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);
    $example_namespace = ['prompt', 'redirect'];
    list($url_builder, $action_token) = $url_builder->acquireParameters(
        $example_namespace,
        "action"
    );

    //the endpoint; act according to parameter
    $query = $DIC->http()->wrapper()->query();
    if($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string());
        if($action === 'redirection') {
            //a response redirecting to an URL
            $target = $df->uri('https://www.ilias.de');
            $response = $factory->prompt()->redirect($target);
        } else {
            //The messagebox we are going to wrap into the prompt
            $redirect = $factory->link()->standard(
                'send redirect command',
                $url_builder->withParameter($action_token, 'redirection')->buildURI()->__toString()
            );

            $message = $factory->messageBox()->info('some message box')
                ->withLinks([$redirect]);
            $response = $factory->prompt()->response($message);
        }
        echo($renderer->renderAsync($response));
        exit();
    }

    //render prompt and button
    $prompt = $factory->prompt()->standard($url_builder->buildURI());
    return $renderer->render([
        $factory->button()->standard('Show Prompt', $prompt->getShowSignal()),
        $prompt
    ]);

}
