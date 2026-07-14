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

use ILIAS\UI\URLBuilder;

require_once __DIR__ . '/base.php';

/**
 * ---
 * description: >
 *   After confirming, the consumer returns a redirect state to leave the prompt
 *   and show feedback on the target page.
 *
 * expected output: >
 *   A button opens a confirmation prompt. Confirming redirects the page and
 *   shows a success message below the trigger button.
 * ---
 */
function redirect(): string
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

    $example_namespace = ['prompt', 'state', 'confirm', 'redirect'];
    $demo_entity_ids = [1, 2, 3];

    [$url_builder, $endpoint_token] = $url_builder->acquireParameters($example_namespace, 'endpoint');
    [$url_builder, $success_token] = $url_builder->acquireParameters($example_namespace, 'success');

    [$url_builder, $confirm_token] = $url_builder->acquireParameters($example_namespace, 'confirm');
    [$url_builder, $process_token] = $url_builder->acquireParameters($example_namespace, 'process');
    [$url_builder, $entities_token] = $url_builder->acquireParameters($example_namespace, 'entities');

    $async_url_builder = $url_builder->withParameter($endpoint_token, 'true');

    if ($query->has($confirm_token->getName())) {
        $entity_ids = retrieveEntityIds($query, $confirm_token->getName(), $refinery);
        if ($entity_ids !== null) {
            $post_url = $async_url_builder->withParameter($process_token, '1');
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
            $parameter_prefix = implode(URLBuilder::SEPARATOR, $example_namespace) . URLBuilder::SEPARATOR;
            $clean_uri = stripExampleParameters(
                $data_factory->uri((string) $http->request()->getUri()),
                $parameter_prefix
            );
            [$redirect_builder, $redirect_success_token] = (new URLBuilder($clean_uri))
                ->acquireParameters($example_namespace, 'success');
            $target = $redirect_builder->withParameter($redirect_success_token, '1')->buildURI();
            echo $renderer->renderAsync($factory->prompt()->state()->redirect($target));
            exit;
        }
    }

    $open_uri = $async_url_builder
        ->withParameter($confirm_token, $demo_entity_ids)
        ->buildURI();

    $prompt = $factory->prompt()->standard($open_uri);
    $trigger = $factory->button()->primary('Open confirm (redirect)', $prompt->getShowSignal($open_uri));

    $has_success_feedback = $query->has($success_token->getName())
        && $query->retrieve($success_token->getName(), $refinery->kindlyTo()->string()) !== '';

    $components = [$trigger, $prompt];
    if ($has_success_feedback) {
        array_unshift(
            $components,
            $factory->messageBox()->success('Action confirmed. Entity ids were processed.')
        );
    }

    $is_async = !$has_success_feedback
        && $query->has($endpoint_token->getName())
        && $query->retrieve($endpoint_token->getName(), $refinery->kindlyTo()->string()) === 'true';

    if (!$is_async) {
        return $renderer->render($components);
    }

    return '';
}

/**
 * Remove all example-specific query parameters before building a redirect target.
 */
function stripExampleParameters(\ILIAS\Data\URI $uri, string $parameter_prefix): \ILIAS\Data\URI
{
    $parameters = array_filter(
        $uri->getParameters(),
        static fn(string $key): bool => !str_starts_with($key, $parameter_prefix),
        ARRAY_FILTER_USE_KEY
    );

    return $uri->withParameters($parameters);
}
