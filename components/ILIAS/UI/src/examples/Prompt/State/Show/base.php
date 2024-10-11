<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Prompt\State\Show;

use ILIAS\UI\Component\Prompt\IsPromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example displays the HTML of a State.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //a response may contain Components implementing IsPromptContent interface.
    $content = $factory->input()->container()->form()->standard(
        $url_builder->buildURI()->__toString(),
        [$factory->input()->field()->text("Text Input")]
    );

    $response = $factory->prompt()->state()->show($content);

    //endpoint to return response on (asynch) call
    $refinery = $DIC['refinery'];
    $example_namespace = ['prompt', 'response'];
    list($url_builder, $url_token) = $url_builder->acquireParameters(
        $example_namespace,
        "response"
    );
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($url_token->getName())) {
        echo($renderer->renderAsync($response));
        exit();
    }

    //build the prompt
    $prompt = $factory->prompt()->standard($url_builder->buildURI());
    $show_button = $factory->button()->standard('Show Prompt', $prompt->getShowSignal());


    //show the response contents:
    $txt_response = $factory->legacy(
        '<pre>'
        . htmlentities($renderer->render($response))
        . '</pre>'
    );

    return $renderer->render([
       $txt_response,
       $show_button,
       $prompt
    ]);

}
