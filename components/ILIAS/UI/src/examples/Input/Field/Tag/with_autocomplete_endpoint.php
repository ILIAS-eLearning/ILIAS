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

namespace ILIAS\UI\examples\Input\Field\Tag;

use ILIAS\UI\URLBuilder;
use ILIAS\Filesystem\Stream\Streams;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic tag input field and attach it to a
 *   form. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Tag Input with Autocomplete". A completion of
 *   the tags will be displayed by ILIAS if an A, B, I or R is typed into the field.
 *   It is also possible to insert tags of your own and confirm those through hitting
 *   the Enter button on your keyboard. Afterwards the tags will be highlighted with color.
 *   An "X" is displayed directly next to each tag. Clicking the "X" will remove the tag.
 *   Clicking "Save" will reload the page and will set the Tag in the input field back to "Interesting".
 * ---
 */
function with_autocomplete_endpoint()
{
    /** @var \ILIAS\DI\Container $DIC */
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $http = $DIC->http();

    $df = new \ILIAS\Data\Factory();

    [$url_builder, $term_token] = (new URLBuilder($df->uri($http->request()->getUri()->__toString())))
        ->acquireParameter(['examples'], 'term');

    $search_term = $http->wrapper()->query()->retrieve(
        $term_token->getName(),
        $refinery->byTrying([
            $refinery->kindlyTo()->string(),
            $refinery->always('')
        ])
    );

    if ($search_term !== '') {
        $response = json_encode(
            array_reduce(
                ['Interesting', 'Boring', 'Animating', 'Repetitious'],
                static function (array $c, string $v) use ($refinery, $search_term): array {
                    if (stristr($v, $search_term)) {
                        $c[] = [
                            'value' => urlencode($refinery->encode()->htmlSpecialCharsAsEntities()->transform($v)),
                            'display' => $v,
                            'searchBy' => $v
                        ];
                    }
                    return $c;
                },
                []
            )
        );
        $http->saveResponse(
            $http->response()->withBody(
                Streams::ofString($response)
            )
        );
        $http->sendResponse();
        $http->close();
    }

    $tag_input = $ui->input()->field()->tag(
        "Tag Input with Autocomplete",
        []
    )->withAsyncAutocomplete(
        $url_builder,
        $term_token
    )->withUserCreatedTagsAllowed(false);

    return  $renderer->render(
        $ui->input()->container()->form()->standard("#", [$tag_input])
    );
}
