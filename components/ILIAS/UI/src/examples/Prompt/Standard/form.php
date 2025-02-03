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

namespace ILIAS\UI\examples\Prompt\Standard;

use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   This shows a Form being used in a Prompt.
 *
 * expected output: >
 *   Initially, there is but a button to open the Prompt.
 *   Clicking the button, a Prompt is shown with a required Text Input.
 *   Leave it blank and click 'Save', the Form's error messages should be shown.
 *   Enter any string and click 'Save' again; the error disappears, but the
 *   Prompt and Form are still shown.
 *   Finally, enter the word 'close' and save again. The Prompt closes.
 * ---
 */
function form()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //when expecting a state, we do not want to render other examples
    $example_namespace = ['prompt', 'endpoints'];
    list($url_builder, $endpointtoken) = $url_builder->acquireParameters($example_namespace, "endpoint");
    $url_builder = $url_builder->withParameter($endpointtoken, "true");

    //build the prompt
    $query_namespace = ['prompt', 'example2'];
    list($url_builder, $action_token) = $url_builder->acquireParameters($query_namespace, "action");
    $url_builder = $url_builder->withParameter($action_token, "form");
    $prompt = $factory->prompt()->standard($url_builder->buildURI());

    //fill the state according to (query-)parameters
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_token->getName())
        && $query->has($action_token->getName())
        && $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string()) === 'form'
    ) {

        //setup a form.
        $uri = $url_builder->buildURI()->__toString();
        $form = $factory->input()->container()->form()->standard(
            $uri,
            [
                $factory->input()->field()->text(
                    "Text Input",
                    "write 'close' to close the prompt."
                )->withRequired(true)
            ]
        );

        //set the response
        $response = $factory->prompt()->state()->show($form);

        $request = $DIC->http()->request();
        if ($request->getMethod() === 'POST') {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if ($data !== null && reset($data) === 'close') {
                /**
                 * alternatively:
                 * $response = $response->withCloseModal(true);
                 */
                $response = $factory->prompt()->state()->close();
            } else {
                $response = $factory->prompt()->state()->show($form);
            }
        }
        $response = $response->withTitle('prompt form example');
        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Prompt with Form', $prompt->getShowSignal());
        return $renderer->render([$prompt, $show_button]);
    }
}
