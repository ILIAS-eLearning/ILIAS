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

namespace ILIAS\UI\examples\Button\Close;

/**
 * ---
 * description: >
 *   This example shows a scenario in which the Close Button is used in an overlay
 *   as indicated in the purpose description. Note that in the Modal the Close Button
 *   is properly placed in the top right corner.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Close Button Demo". Clicking the button will open a modal with text and a Close-Button.
 *   A click onto the button will close the modal.
 * ---
 */
function modal()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip(
        'Close Button Demo',
        $factory->legacy()->content('See the Close Button in the top right corner.')
    );
    $button1 = $factory->button()->standard('Show Close Button Demo', '#')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button1, $modal]);
}
