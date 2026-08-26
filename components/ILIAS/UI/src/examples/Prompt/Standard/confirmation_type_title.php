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
 *   A confirmation prompt for deleting a single object without Entity Input.
 *   The user must re-type the object title in a Text Input to confirm.
 *   A confirmation Message Box as sibling Prompt content can be added once
 *   secondary Prompt content is available (follow-up to GitHub issue 11105).
 *
 * expected output: >
 *   A button opens a prompt with a form asking to type the object title.
 *   Submitting with a wrong or empty title keeps the prompt open and shows
 *   an error on the text input.
 *   Typing the exact title and submitting closes the prompt.
 * ---
 */
function confirmation_type_title(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $query = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $object_title = 'Introduction to ILIAS';

    $here_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($here_uri);

    $example_namespace = ['prompt', 'confirmation_type_title'];
    [$url_builder, $endpoint_token] = $url_builder->acquireParameters($example_namespace, 'endpoint');
    $url_builder = $url_builder->withParameter($endpoint_token, 'true');

    [$url_builder, $action_token] = $url_builder->acquireParameters($example_namespace, 'action');
    $form_uri = $url_builder->withParameter($action_token, 'form')->buildURI();

    $build_form = static function (string $post_url) use ($factory, $refinery, $object_title) {
        $title_field = $factory->input()->field()->text(
            'Object title',
            sprintf('Type "%s" to confirm deletion.', $object_title)
        )->withRequired(true)
            ->withAdditionalTransformation(
                $refinery->custom()->constraint(
                    static fn(mixed $value): bool => is_string($value) && $value === $object_title,
                    sprintf('The title must match "%s" exactly.', $object_title)
                )
            );

        return $factory->input()->container()->form()->standard(
            $post_url,
            [$title_field]
        )->withSubmitLabel('Delete');
    };

    if ($query->has($action_token->getName()) &&
        $query->retrieve($action_token->getName(), $refinery->kindlyTo()->string()) === 'form'
    ) {
        $form = $build_form((string) $form_uri);
        $title = sprintf('Delete "%s"?', $object_title);

        if ($http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($http->request());
            if ($form->getData() !== null) {
                echo $renderer->renderAsync($factory->prompt()->state()->close());
                exit;
            }
        }

        echo $renderer->renderAsync(
            $factory->prompt()->state()->show($form)->withTitle($title)
        );
        exit;
    }

    $prompt = $factory->prompt()->standard($form_uri);
    $trigger = $factory->button()->primary(
        'Open delete confirmation (type title)',
        $prompt->getShowSignal($form_uri)
    );

    if (!$query->has($endpoint_token->getName())) {
        return $renderer->render([$prompt, $trigger]);
    }

    return '';
}
