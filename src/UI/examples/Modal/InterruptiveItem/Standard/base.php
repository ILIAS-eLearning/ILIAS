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

namespace ILIAS\UI\examples\Modal\InterruptiveItem\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard interruptive item modal.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show some key-value interruptive Items".
 *   A click onto the button will grey out ILIAS, open a modal titled "My Title" and the content "Here you see some
 *   key-value interruptive items:". Three values will be displayed (First Key, Second Key, Third Key).
 *   Two buttons "Delete" and "Cancel" are also rendered.
 *   A click onto the "Delete" button will relaod the page.
 *   A click onto the "Cancel" button will hide the modal.
 *   You can also leave the modal by clicking onto the greyed out ILIAS in the background outside of the modal.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see some standard interruptive items:';
    $icon = $factory->image()->standard('./templates/default/images/standard/icon_crs.svg', '');
    $modal = $factory->modal()->interruptive('My Title', $message, "#")
                     ->withAffectedItems(array(
                         $factory->modal()->interruptiveItem()->standard(
                             '10',
                             'Title of the Item',
                             $icon,
                             'Note, this item is currently only to be used in interruptive Modal.'
                         ),
                         $factory->modal()->interruptiveItem()->standard(
                             '20',
                             'Title of the other Item',
                             $icon,
                             'And another one.'
                         )
                     ));
    $button = $factory->button()->standard('Show some standard interruptive items', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
