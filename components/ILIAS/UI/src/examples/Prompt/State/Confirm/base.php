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

namespace ILIAS\UI\examples\Prompt\State\Confirm;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *   After confirming, the consumer returns a show state with new prompt content.
 *
 * expected output: >
 *   A button opens a Prompt with confirmation question, entity listing and actions.
 *   Confirming posts the entity ids; a success message is shown inside the prompt.
 * ---
 */
function base(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $query = $http->wrapper()->query();
    $refinery = $DIC['refinery'];
    $data_factory = new \ILIAS\Data\Factory();

    $here_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($here_uri);

    $example_namespace = ['prompt', 'state', 'confirm'];
    $demo_entity_ids = [1, 2, 3];

    [$url_builder, $endpoint_token] = $url_builder->acquireParameters($example_namespace, 'endpoint');
    $url_builder = $url_builder->withParameter($endpoint_token, 'true');

    [$url_builder, $confirm_token] = $url_builder->acquireParameters($example_namespace, 'confirm');
    [$url_builder, $process_token] = $url_builder->acquireParameters($example_namespace, 'process');
    [$url_builder, $entities_token] = $url_builder->acquireParameters($example_namespace, 'entities');

    if ($query->has($confirm_token->getName())) {
        $entity_ids = retrieveEntityIds($query, $confirm_token->getName(), $refinery);
        if ($entity_ids !== null) {
            $post_url = $url_builder->withParameter($process_token, '1');
            $state = $factory->prompt()->state()->confirm(
                new ConfirmStateEntityRetrieval(),
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

    if ($http->request()->getMethod() === 'POST' && $query->has($process_token->getName())) {
        $process = $query->retrieve($process_token->getName(), $refinery->kindlyTo()->string());
        if ($process !== '') {
            $entity_ids = retrieveEntityIds($query, $entities_token->getName(), $refinery) ?? [];
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
    $trigger = $factory->button()->primary('Open confirm (show result)', $prompt->getShowSignal($open_uri));

    if (!$query->has($endpoint_token->getName())) {
        return $renderer->render([$trigger, $prompt]);
    }

    return '';
}

/**
 * @return array<int>|null
 */
function retrieveEntityIds(
    \ILIAS\HTTP\Wrapper\RequestWrapper $query,
    string $parameter_name,
    \ILIAS\Refinery\Factory $refinery,
): ?array {
    if (!$query->has($parameter_name)) {
        return null;
    }

    $raw = $query->retrieve(
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

class ConfirmStateEntityRetrieval implements EntityRetrieval
{
    public function getEntities(
        \ILIAS\UI\Factory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        foreach ([1, 2, 3] as $entity_id) {
            yield $this->getPseudoEntity($ui_factory, $entity_id);
        }
    }

    public function getEntitiesByIds(
        \ILIAS\UI\Factory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            yield $this->getPseudoEntity($ui_factory, (int) $entity_id);
        }
    }

    protected function getPseudoEntity(\ILIAS\UI\Factory $ui_factory, int $entity_id): Entity
    {
        return $ui_factory->entity()->standard($entity_id, "Entity $entity_id", '');
    }
}
