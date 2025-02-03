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

use ILIAS\UI\Component\Prompt\PromptContent;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   This shows how different states are being used in the same Prompt
 *   according to parameters, thus creating an 'internally navigational' Prompt.
 *   the additional buttons demonstrate the usage of JS within Prompts.
 *
 * expected output: >
 *   Initially, there are three buttons to open the Prompt.
 *   The Prompt will display several links. Clicking a Link will display the
 *   clicked number in the content, along with a "back"-Button.
 * ---
 */
function parameters()
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

    //build a prompt
    $query_namespace = ['prompt', 'example1'];
    list($url_builder, $action_token, $amount_token) = $url_builder->acquireParameters($query_namespace, "action", "amount");
    $url_builder = $url_builder->withParameter($action_token, "showprompt");

    $default_uri = $url_builder->withParameter($amount_token, "1")->buildURI();
    $prompt = $factory->prompt()->standard($default_uri);

    //build some endpoint
    $query = $DIC->http()->wrapper()->query();
    $icon = $factory->symbol()->icon()->standard('someExample', 'Example');
    $item = $icon;
    $links = [];

    if ($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string());
        $amount = $query->retrieve($amount_token->getName(), $refinery->kindlyTo()->int()) ;
        switch ($action) {
            case 'showprompt':
                foreach (range(1, $amount) as $idx) {
                    $link_uri = $url_builder
                        ->withParameter($action_token, "promptlink")
                        ->withParameter($amount_token, (string) $idx)
                        ->buildURI()->__toString();
                    $links[] = $factory->link()->standard((string) $idx, $link_uri);
                }
                $buttons = [
                    $factory->button()->standard('OK', '#')->withOnLoadCode(
                        fn($id) => "$('#$id').on('click', (e)=> {alert('$id');});"
                    )
                ];
                $prompt_content = $factory->messageBox()->info('some text')
                    ->withLinks($links)
                    ->withButtons($buttons);
                break;

            case 'promptlink':
                $back_uri = $url_builder
                        ->withParameter($action_token, "showprompt")
                        ->withParameter($amount_token, (string) $amount)
                        ->buildURI()->__toString();
                $back = $factory->button()->standard('back', '#')->withOnLoadCode(
                    fn($id) => "$('#$id').on('click', (e)=> {
                        let promptId = e.target.closest('.il-prompt').id;
                        il.UI.prompt.get(promptId).show('$back_uri');
                    });"
                );
                $prompt_content = $factory->messageBox()->info((string) $amount)
                    ->withButtons([$back]);
                break;

            default:
                throw new \Exception('?' . $action . $amount);
        }

        $response = $factory->prompt()->state()->show($prompt_content);
        echo($renderer->renderAsync($response));
        exit();
    }

    if (!$query->has($endpointtoken->getName())) {
        $show_button = $factory->button()->standard('Show Simple Prompt', $prompt->getShowSignal());
        $close_button = $factory->button()->standard('close Simple Prompt', $prompt->getCloseSignal());

        $uri = $url_builder->withParameter($amount_token, "8")->buildURI();
        $show_button8 = $factory->button()->standard('Show Prompt with Parameter', $prompt->getShowSignal($uri));

        $uri = $url_builder->withParameter($amount_token, "78")->buildURI();
        $show_button78 = $factory->button()->standard('Show Prompt with a lot of Items', $prompt->getShowSignal($uri));

        return $renderer->render([
            $prompt,
            $show_button, $show_button8, $show_button78
        ]);
    }
}
