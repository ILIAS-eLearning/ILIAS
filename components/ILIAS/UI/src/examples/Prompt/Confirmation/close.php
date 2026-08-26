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

namespace ILIAS\UI\examples\Prompt\Confirmation;

use ILIAS\UI\URLBuilder;

require_once __DIR__ . '/base.php';

/**
 * ---
 * description: >
 *   After confirming, the consumer returns a close state and the prompt is dismissed.
 *
 * expected output: >
 *   A button opens a confirmation prompt. Confirming closes the prompt.
 *   No further information is displayed.
 *   The prompt can also be closed.
 * ---
 */
function close(): string
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

    $example_namespace = ['prompt', 'confirmation', 'close'];
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
            $confirmation = $factory->prompt()->confirmation(
                new ConfirmationExampleEntityRetrieval(),
                $post_url,
                $entities_token,
                $entity_ids,
                'Are you sure you want to perform this action?',
                'Performing some action',
            );

            echo $renderer->renderAsync($factory->prompt()->state()->show($confirmation));
            exit;
        }
    }

    if ($http->request()->getMethod() === 'POST' && $query->has($process_token->getName())) {
        $process = $query->retrieve($process_token->getName(), $refinery->kindlyTo()->string());
        if ($process !== '') {
            echo $renderer->renderAsync($factory->prompt()->state()->close());
            exit;
        }
    }

    $open_uri = $url_builder
        ->withParameter($confirm_token, $demo_entity_ids)
        ->buildURI();

    $prompt = $factory->prompt()->standard($open_uri);
    $trigger = $factory->button()->primary('Open Confirmation (And Close Prompt)', $prompt->getShowSignal($open_uri));
    $hint = $factory->messageBox()->info('After confirming, the prompt closes.');

    if (!$query->has($endpoint_token->getName())) {
        return $renderer->render([$hint, $trigger, $prompt]);
    }

    return '';
}
