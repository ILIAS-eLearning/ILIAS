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

use ILIAS\UI\Component\Prompt\IsPromptContent;
use ILIAS\UI\URLBuilder;
use Generator;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *   Example showing how the deletion of a single Entity is confirmed using a Prompt.
 *   It includes minimal data processing.
 *
 * expected output: >
 *   ILIAS initially shows a button. When clicked, a dialog opens with a Confirmation
 *   Message Box and a Form visible. Inside the Form there is one Input Field which
 *   must be used to enter the Entity's name and confirm the action. At the bottom of
 *   the dialog a button can be found to submit the form. When clicked, the dialog is
 *   updated and either a Success Message Box is shown, or the Form with all errors.
 * ---
 */
function single_entity_confirmation(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $http = $DIC->http();
    $server_request = $DIC->http()->request();
    $get_request = $DIC->http()->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();

    $pseudo_data = [
        67 => ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
    ];

    $here_url = $data_factory->uri((string) $DIC->http()->request()->getUri());
    $here_url_builder = new URLBuilder($here_url);

    // will simulate a GET request to this example asking for a confirmation prompt state
    // for the entity in $pseudo_data
    [$confirmation_get_url, $entity_id_parameter] = $here_url_builder->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "to_process");
    $confirmation_get_url = $confirmation_get_url->withParameter($entity_id_parameter, (string) array_key_first($pseudo_data));

    // will simulate a POST request to this example that processes the entity in $pseudo_data.
    // this example requires the entity id in both url and body of the POST request.
    [$confirmation_post_url, $process_parameter] = $confirmation_get_url->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "process", "1");

    // will simulate a shared function to create a Form that is accessible in all endpoints.
    // notice how $entity_id is mandatory here, because we perform validation.
    $build_form = static fn(string $entity_id) => $factory->input()->container()->form()->standard(
        (string) $confirmation_post_url->buildURI(),
        [
            $entity_id_parameter->getName() => $factory->input()->field()->hidden()->withValue($entity_id),
            "entity_text_identifier" => $factory
                ->input()->field()->text(
                    "Enter entity name",
                    sprintf("Type '%s' to confirm deletion.", $pseudo_data[$entity_id][3])
                )
                ->withRequired(true, $refinery->logical()->sequential([
                    $refinery->string()->hasMinLength(1),
                    $refinery->custom()->constraint(
                        static fn(string $value) => ($value === $pseudo_data[$entity_id][3]),
                        sprintf("Input must match '%s' exactly.", $pseudo_data[$entity_id][3]),
                    )
                ]))
        ],
    )->withSubmitLabel('Delete');

    // simulates the POST endpoint for processing the confirmed entities
    if ($get_request->has($process_parameter->getName()) && $get_request->has($entity_id_parameter->getName())) {
        // retrieve requested entity id from GET request
        $requested_entity_id = $get_request->retrieve($entity_id_parameter->getName(), $refinery->kindlyTo()->string());

        // create the same Form that carried the entity id as Hidden Field
        $form = $build_form($requested_entity_id);

        // provide the Form with the current request and fetch the entity ids from its body
        $form = $form->withRequest($server_request);
        $data = $form->getData();
        $confirmed_entity_id = $data[$entity_id_parameter->getName()] ?? null;
        $confirmed_entity_text = $data['entity_text_identifier'] ?? null;

        if (null === $confirmed_entity_id || $confirmed_entity_text !== $pseudo_data[$confirmed_entity_id][3]) {
            // create a Show Prompt State that shows the validated form
            $prompt_state = $factory->prompt()->state()->show($form);
        } else {
            // here you should process the $confirmed_entity_ids as you see fit and
            // validate them beforehand, as we must not trust clients
            // ...

            // create a Success Message Box asking the proper question and make
            // sure to integrate the amount of entities as a number
            $message_box = $factory->messageBox()->success(
                sprintf("Successfully deleted %s.", $pseudo_data[$confirmed_entity_id][3]),
            );

            // create a Show Prompt State to instruct the client to render the Message Box
            $prompt_state = $factory->prompt()->state()->show($message_box);
        }

        // render the Prompt State and send it asynchronously to the client.
        $html = $renderer->renderAsync($prompt_state);
        $http->saveResponse(
            $http->response()
                 ->withHeader('Content-Type', 'text/html; charset=utf-8')
                 ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    // simulates the GET endpoint for rendering the prompt state
    if ($get_request->has($entity_id_parameter->getName())) {
        // retrieve requested entity id from GET request
        $requested_entity_id = $get_request->retrieve($entity_id_parameter->getName(), $refinery->kindlyTo()->string());

        // create a Form that carries the entity id as Hidden Field
        $form = $build_form($requested_entity_id);

        // create a Confirmation Message Box asking the proper question and make
        // sure to integrate the entity type and a textual identifier
        $message_box = $factory->messageBox()->confirmation(
            sprintf("Do you want to delete Entity %s?", $pseudo_data[$requested_entity_id][3]),
        );

        // create a Show Prompt State to instruct the client to render all of above and
        // make sure $form is the primary prompt content
        $prompt_state = $factory->prompt()->state()->show($form, $message_box);

        // render the Prompt State and send it asynchronously to the client.
        $html = $renderer->renderAsync($prompt_state);
        $http->saveResponse(
            $http->response()
                 ->withHeader('Content-Type', 'text/html; charset=utf-8')
                 ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    // create a Standard Prompt that requests the simulated GET endpoint
    $prompt = $factory->prompt()->standard($confirmation_get_url->buildURI())->withTitle('Confirm deletion');

    // create a Button that triggers the Standard Prompt
    $button = $factory->button()->primary('Delete entity', $prompt->getShowSignal());

    return $renderer->render([$button, $prompt]);
}
