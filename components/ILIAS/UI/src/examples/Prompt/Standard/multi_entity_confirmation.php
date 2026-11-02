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
 *   Example showing how the deletion of multiple Entity's are confirmed using a
 *   Prompt. It includes minimal data processing.
 *
 * expected output: >
 *   ILIAS initially shows a button. When clicked, a dialog opens with a Confirmation
 *   Message Box and an Entity Listing visible. At the bottom of the dialog a button
 *   can be found to confirm and perform the action. When clicked, the dialog is updated
 *   and a Success Message Box is shown.
 * ---
 */
function multi_entity_confirmation(): string
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
        13 => ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
        42 => ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
        67 => ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
        99 => ['kj', 'katiejones', 'katiejones@example.com', 'Katie Jones', '2022-03-17 15:30:50', true]
    ];

    $here_url = $data_factory->uri((string) $DIC->http()->request()->getUri());
    $here_url_builder = new URLBuilder($here_url);

    // will simulate a GET request to this example asking for a confirmation prompt state
    // for the entities in $pseudo_data
    [$confirmation_get_url, $entity_id_parameter] = $here_url_builder->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "to_process");
    $confirmation_get_url = $confirmation_get_url->withParameter($entity_id_parameter, array_keys($pseudo_data));

    // will simulate a POST request to this example that processes the entities in $pseudo_data
    [$confirmation_post_url, $process_parameter] = $here_url_builder->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "process", "1");

    // will simulate a shared function to create a Form that is accessible in all endpoints
    $build_form = static fn(?array $entity_ids = null) => $factory->input()->container()->form()->standard(
        (string) $confirmation_post_url->buildURI(),
        [$entity_id_parameter->getName() => $factory->input()->field()->hiddenList()->withValue($entity_ids ?? [])],
    )->withSubmitLabel('Delete');

    // simulates the POST endpoint for processing the confirmed entities
    if ($get_request->has($process_parameter->getName())) {
        // create the same Form that carried the entity ids as Hidden List Field
        $form = $build_form();

        // provide the Form with the current request and fetch the entity ids from its body
        $confirmed_entity_ids = $form->withRequest($server_request)->getData()[$entity_id_parameter->getName()] ?? null;

        // here you should process the $confirmed_entity_ids as you see fit and
        // validate them beforehand, as we must not trust clients
        foreach ($confirmed_entity_ids as $entity_id) {
            if ($entity_id) {
            }
        }

        // create a Success Message Box asking the proper question and make
        // sure to integrate the amount of entities as a number
        $message_box = $factory->messageBox()->success(
            sprintf("Successfully deleted %d entities.", count($confirmed_entity_ids)),
        );

        // create a Show Prompt State to instruct the client to render the Message Box
        $prompt_state = $factory->prompt()->state()->show($message_box);

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
        // retrieve requested entity ids from GET request
        $requested_entity_ids = $get_request->retrieve(
            $entity_id_parameter->getName(),
            $refinery->kindlyTo()->listOf($refinery->kindlyTo()->int())
        );

        // create a Form that carries the entity ids as Hidden List Field
        $form = $build_form($requested_entity_ids);

        // create a Entity Listing that visualises the subset entities
        $listing = $factory->listing()->entity()->standard(new ExampleEntityRetrieval($pseudo_data))->withSubset(
            $requested_entity_ids
        );

        // create a Confirmation Message Box asking the proper question and make
        // sure to integrate the amount of entities as a number
        $message_box = $factory->messageBox()->confirmation(
            sprintf("Do you want to delete %d entities?", count($requested_entity_ids)),
        );

        // create a Show Prompt State to instruct the client to render all of above and
        // make sure $form is the primary prompt content
        $prompt_state = $factory->prompt()->state()->show($form, $message_box, $listing);

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
    $button = $factory->button()->primary('Delete entities', $prompt->getShowSignal());

    return $renderer->render([$button, $prompt]);
}

/** @noinspection AutoloadingIssuesInspection */
class ExampleEntityRetrieval implements EntityRetrieval
{
    public function __construct(protected array $dataset)
    {
    }

    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        foreach (array_keys($this->dataset) as $entity_id) {
            yield $this->createEntity($ui_factory, $entity_id);
        }
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            yield $this->createEntity($ui_factory, $entity_id);
        }
    }

    protected function createEntity(\ILIAS\UI\Factory $ui_factory, string|int $entity_id): Entity
    {
        if (!isset($this->dataset[$entity_id])) {
            return $ui_factory->entity()->standard($entity_id, 'Unknown', 'Unknown');
        }

        [$initials, $login, $email, $name, $last_seen, $active] = $this->dataset[$entity_id];

        return $ui_factory
            ->entity()
            ->standard($entity_id, $name, $ui_factory->symbol()->avatar()->letter($initials))
            ->withMainDetails(
                $ui_factory
                    ->listing()
                    ->property()
                    ->withProperty('login', $login)
                    ->withProperty('email', $email)
            );
    }
}
