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

namespace ILIAS\UI\examples\Modal\RoundTrip;

/**
 * ---
 * description: >
 *   Example for rendering a round trip modal.
 *
 * expected output: >
 *   ILIAS shows a button titled "open modal". A click onto the button opens the modal including a formular "some text"
 *   and "some number". The entered content will be displayed above the button after hitting "Save".
 * ---
 */
function show_form_in_modal()
{
    global $DIC;

    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $factory = $DIC->ui()->factory();

    // declare roundtrip with inputs and form action.
    $modal = $factory->modal()->roundtrip(
        'roundtrip with form',
        null,
        [
            $factory->input()->field()->text('some text'),
            $factory->input()->field()->numeric('some numbere'),
        ],
        '#'
    );

    // declare something that triggers the modal.
    $open = $factory->button()->standard('open modal', '#')->withOnClick($modal->getShowSignal());

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ('POST' === $request->getMethod()) {
        $modal = $modal->withRequest($request);
        $data = $modal->getData();
    } else {
        $data = 'no results yet.';
    }

    return
        '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render([$open, $modal]);
}
