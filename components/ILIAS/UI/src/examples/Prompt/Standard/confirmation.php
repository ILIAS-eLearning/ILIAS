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

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *   A confirmation prompt for deleting or changing multiple entities.
 *   The UI framework composes message box, entity listing and a Standard Form
 *   with Kitchen Sink Hidden Inputs.
 *
 * expected output: >
 *   A button opens a prompt listing selected entities with a confirmation question.
 *   Submitting posts the entity ids via the Standard Form (POST body); the result is shown in the prompt.
 *   The prompt can be closed.
 * ---
 */
function confirmation(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $query = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($here_uri);

    $example_namespace = ['prompt', 'confirmation'];
    $demo_entity_ids = [1, 2, 3];

    // when expecting a state, we do not want to render other examples on the same page
    [$url_builder, $endpoint_token] = $url_builder->acquireParameters($example_namespace, 'endpoint');
    $url_builder = $url_builder->withParameter($endpoint_token, 'true');

    [$url_builder, $confirm_token] = $url_builder->acquireParameters($example_namespace, 'confirm');
    [$url_builder, $process_token] = $url_builder->acquireParameters($example_namespace, 'process');
    [$url_builder, $entities_token] = $url_builder->acquireParameters($example_namespace, 'entities');

    // async GET: load confirmation prompt state
    if ($query->has($confirm_token->getName())) {
        $entity_ids = retrieveEntityIds($query, $confirm_token->getName(), $refinery);
        if ($entity_ids !== null) {
            $post_url = $url_builder->withParameter($process_token, '1');
            $state = $factory->prompt()->state()->confirm(
                new ConfirmationEntityRetrieval(),
                $post_url,
                $entities_token,
                $entity_ids,
                'Are you sure you want to perform this action?',
                'Performing some action',
            );

            echo $renderer->renderAsync($state);
            exit;
        }
    }

    // async POST: form submit after confirmation
    if ($http->request()->getMethod() === 'POST' && $query->has($process_token->getName())) {
        $process = $query->retrieve($process_token->getName(), $refinery->kindlyTo()->string());
        if ($process !== '') {
            $state = $factory->prompt()->state()->confirm(
                new ConfirmationEntityRetrieval(),
                $url_builder->withParameter($process_token, '1'),
                $entities_token,
                $demo_entity_ids,
                'Are you sure you want to perform this action?',
                'Performing some action',
            );
            $entity_ids = $state->getConfirmedData($http->request());
            $message = $factory->messageBox()->success(
                'Submitted entity ids: ' . implode(', ', array_map('strval', $entity_ids))
            );
            echo $renderer->renderAsync(
                $factory->prompt()->state()->show($message)->withTitle('Confirmation result')
            );
            exit;
        }
    }

    $open_uri = $url_builder
        ->withParameter($confirm_token, $demo_entity_ids)
        ->buildURI();

    $prompt = $factory->prompt()->standard($open_uri);
    $trigger = $factory->button()->primary('Open confirmation prompt', $prompt->getShowSignal($open_uri));

    if (!$query->has($endpoint_token->getName())) {
        return $renderer->render([$prompt, $trigger]);
    }

    return '';
}

/**
 * @return array<int>|null
 */
function retrieveEntityIds(
    \ILIAS\HTTP\Wrapper\RequestWrapper $wrapper,
    string $parameter_name,
    \ILIAS\Refinery\Factory $refinery,
): ?array {
    if (!$wrapper->has($parameter_name)) {
        return null;
    }

    $raw = $wrapper->retrieve(
        $parameter_name,
        $refinery->custom()->transformation(static fn(mixed $value): mixed => $value)
    );

    if (!is_array($raw)) {
        $raw = ($raw === '' || $raw === null) ? [] : [$raw];
    }

    $raw = array_values(array_filter(
        $raw,
        static fn(mixed $value): bool => $value !== '' && $value !== null
    ));

    if ($raw === []) {
        return null;
    }

    return $refinery->kindlyTo()->listOf($refinery->kindlyTo()->int())->transform($raw);
}

class ConfirmationEntityRetrieval implements EntityRetrieval
{
    protected array $data = [
        ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
        ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
        ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
        ['kj', 'katiejones', 'katiejones@example.com', 'Katie Jones', '2022-03-17 15:30:50', true],
    ];

    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        foreach ($this->data as $index => $record) {
            yield $this->mapRecord($ui_factory, $index, $record);
        }
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            if (!isset($this->data[$entity_id])) {
                continue;
            }
            yield $this->mapRecord($ui_factory, $entity_id, $this->data[$entity_id]);
        }
    }

    protected function mapRecord(\ILIAS\UI\Factory $ui_factory, int|string $id, array $record): Entity
    {
        [$abbreviation, $login, $email, $name, $last_seen, $active] = $record;
        $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);

        return $ui_factory->entity()->standard($id, $name, $avatar)
            ->withMainDetails(
                $ui_factory->listing()->property()
                    ->withProperty('login', $login)
                    ->withProperty('mail', $email, false)
            )
            ->withDetails(
                $ui_factory->listing()->property()
                    ->withItems([
                        ['last seen', $last_seen],
                        ['active', $active ? 'yes' : 'no'],
                    ])
            );
    }
}
