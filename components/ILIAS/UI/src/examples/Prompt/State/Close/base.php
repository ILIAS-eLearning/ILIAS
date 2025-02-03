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

namespace ILIAS\UI\examples\Prompt\State\Close;

use ILIAS\UI\Component\Prompt\IsPromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example demonstrates how to use commands in the Response.
 *
 * expected output: >
 *   When clicking the button, the Prompt shows a Message Box with a Link.
 *   Using the Link will close the Prompt.
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
    $example_namespace = ['prompt', 'close'];
    list($url_builder, $action_token) = $url_builder->acquireParameters(
        $example_namespace,
        "action"
    );

    //the endpoint; act according to parameter
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string());
        if ($action === 'closecommand') {
            //a state to simply close the modal
            $response = $factory->prompt()->state()->close();
        } else {
            //The messagebox we are going to wrap into the prompt
            $close = $factory->link()->standard(
                'send close command',
                $url_builder->withParameter($action_token, 'closecommand')->buildURI()->__toString()
            );

            $message = $factory->messageBox()->info('some message box')
                ->withLinks([$close]);
            $response = $factory->prompt()->state()->show($message);
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
