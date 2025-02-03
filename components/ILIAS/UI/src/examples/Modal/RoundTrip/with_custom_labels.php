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
 *   An example showing how you can set a custom label for the
 *   modals cancel-button.
 *
 * expected output: >
 *   ILIAS shows a button titled "I will show you something". A click onto the button will open a modal including the two
 *   buttons with custom labels.
 * ---
 */
function with_custom_labels()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip(
        'Showing something off',
        [
            $factory->messageBox()->info('I am something.'),
        ]
    )->withCancelButtonLabel(
        'Thank you and goodbye'
    )->withActionButtons([$factory->button()->standard('Nothing todo here', '#')]);

    $trigger = $factory->button()->standard('I will show you something', $modal->getShowSignal());

    return $renderer->render([$modal, $trigger]);
}
