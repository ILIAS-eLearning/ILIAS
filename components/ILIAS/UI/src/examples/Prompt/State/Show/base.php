<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Prompt\State\Show;

use ILIAS\UI\Component\Prompt\IsPromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example displays the HTML of a State.
 * expected output: >
 *   HTML is rendered to the preview; it sports several section-tags with a data-section attribute.
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
    $txt_response = $factory->legacy()->content(
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
