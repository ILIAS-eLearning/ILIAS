<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Prompt\Standard;

use ILIAS\UI\Component\Prompt\PromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   This example wraps a Message Box into a Prompt (Response).
 *
 * expected output: >
 *   A Message Box is rendered along with a Button triggering the Prompt.
 *   The action of the Message Box is without function.
 *   When clicking "Show Simple Prompt", a Prompt is shown, making controls
 *   on the original page inaccessible.
 *   The action of the Message Box is removed from the content and shown in
 *   the button-section of the Prompt.
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

    //The messagebox we are going to wrap into the prompt
    $message = $factory->messageBox()->success('some message box')
        ->withButtons([$factory->button()->standard('some Action', '#')]);

    //when expecting a response, we do not want to render other examples
    $example_namespace = ['prompt', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");

    //build the prompt
    $query_namespace = ['prompt', 'example0'];
    list($url_builder, $token) = $url_builder->acquireParameters($query_namespace, "show");
    $url_builder = $url_builder->withParameter($token, "true");
    $prompt = $factory->prompt()->standard($url_builder->buildURI());

    //build the endpoint returning the wrapped message
    $query = $DIC->http()->wrapper()->query();
    if($query->has($token->getName())) {
        $response = $factory->prompt()->response($message);
        echo($renderer->renderAsync($response));
        exit();
    }

    //a button to open the prompt:
    $show_button = $factory->button()->standard('Show Simple Prompt', $prompt->getShowSignal());

    if (!$query->has($endpointtoken->getName())) {
        return $renderer->render([
           $message,
           $prompt,
           $show_button
        ]);
    }
}
