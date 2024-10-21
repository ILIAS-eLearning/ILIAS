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

namespace ILIAS\UI\examples\Modal\Interruptive;

/**
 * ---
 * description: >
 *   An example showing how you can set a custom label for the
 *   modals action- and cancel-button.
 *
 * expected output: >
 *   ILIAS shows a button titled "I will interrupt you".
 *   A click onto the button will grey out ILIAS, opens a modal titled "Interrupting something" and displays the content
 *   "Am I interrupting you?".
 *   Additionally two buttons "Yeah you do!" and "Nah, not really" are rendered.
 *   A click onto "Yeah you do!" will reload the page.
 *   A click onto "Nah, not really" will hide the modal.
 *   You can also leave the modal by clicking onto the greyed out ILIAS in the background outside of the modal.
 * ---
 */
function with_custom_labels()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->interruptive(
        'Interrupting something',
        'Am I interrupting you?',
        '#'
    )->withActionButtonLabel(
        'Yeah you do!'
    )->withCancelButtonLabel(
        'Nah, not really'
    );

    $trigger = $factory->button()->standard('I will interrupt you', $modal->getShowSignal());

    return $renderer->render([$modal, $trigger]);
}
