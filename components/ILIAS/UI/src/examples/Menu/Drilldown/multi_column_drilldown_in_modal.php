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

namespace ILIAS\UI\examples\Menu\Drilldown;

use ILIAS\UI\Implementation\Component\ReplaceSignal;

/**
 * ---
 * description: >
 *   The example shows how a Drilldown Menu works in a modal.
 *
 * expected output: >
 *   When clicking the button, a modal opens displaying the menu:
 *   ILIAS shows a box titled "Animal of the year" and two drilldowns including a hover effect.
 *   Clicking the drilldowns opens up more sub-entries or links pointing to the ILIAS page or a different modal.
 *   Clicking the heading will lead one level back.
 * ---
 */
function multi_column_drilldown_in_modal()
{
    global $DIC;
    /** @var ILIAS\UI\Factory $f */
    $f = $DIC->ui()->factory();
    /** @var ILIAS\UI\Renderer $r */
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $_SERVER['REQUEST_URI'];

    if ($request_wrapper->has('content')) {
        renderModalAsync(
            $request_wrapper->retrieve(
                'content',
                $refinery->kindlyTo()->string()
            )
        );
    }

    $drilldown_modal = $f->modal()->roundtrip(
        'My Modal',
        []
    );

    $drilldown_modal = $drilldown_modal->withAsyncRenderUrl(
        $url . '&content=drilldown&replaceSignal=' . $drilldown_modal->getReplaceSignal()->getId()
    );

    $button = $f->button()->standard('Open Animals', $drilldown_modal->getShowSignal());

    return $r->render([
        $button,
        $drilldown_modal
    ]);
}

function renderModalAsync(string $content): void
{
    global $DIC;
    /** @var ILIAS\UI\Factory $f */
    $f = $DIC->ui()->factory();
    /** @var ILIAS\UI\Renderer $r */
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $_SERVER['REQUEST_URI'];

    $signal_id = "";
    if ($request_wrapper->has('replaceSignal')) {
        $signal_id = $request_wrapper->retrieve('replaceSignal', $refinery->kindlyTo()->string());
    }
    $replace_signal = new ReplaceSignal($signal_id);

    switch ($content) {
        case 'image':
            $image = $f->image()->responsive('assets/ui-examples/images/Image/mountains.jpg', 'Some mountains in the dusk');
            $replace_signal_with_url = $replace_signal->withAsyncRenderUrl(
                $url . '&content=drilldown&replaceSignal=' . $replace_signal->getId()
            );
            $button = $f->button()->standard('Back to Drilldown', '')->withOnClick($replace_signal_with_url);
            echo $r->renderAsync(
                $f->modal()->roundtrip(
                    'My Modal',
                    [$image]
                )->withActionButtons([$button])
            );
            exit;
        default:
            $ico = $f->symbol()->icon()->standard('', '')->withSize('small')->withAbbreviation('+');
            $button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '')
                ->withOnClick($replace_signal->withAsyncRenderUrl($url . '&content=image&replaceSignal=' . $replace_signal->getId()));

            $uri = new \ILIAS\Data\URI('https://ilias.de');
            $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);
            $divider = $f->divider()->horizontal();

            $items = [
                $f->menu()->sub('Switzerland', [
                    $f->menu()->sub('Riverine Amphipod', [$button, $link]),
                    $f->menu()->sub('Wildcat', [
                        $f->menu()->sub('European Wildcat', [$button, $link]),
                        $f->menu()->sub('African Wildcat', [$button, $link, $divider, $link])
                    ]),
                    $button,
                    $link
                ]),

                $f->menu()->sub('Germany', [
                    $f->menu()->sub('Otter', [$button, $link]),
                    $f->menu()->sub('Mole', [$button, $link]),
                    $divider,
                    $f->menu()->sub('Deer', [$button, $link])
                ])
            ];

            echo $r->renderAsync(
                $f->modal()->roundtrip(
                    'My Modal',
                    [$f->menu()->drilldown('Animal of the year', $items)]
                )
            );
            exit;
    }
}
