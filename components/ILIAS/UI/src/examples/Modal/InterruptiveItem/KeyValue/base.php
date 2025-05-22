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

namespace ILIAS\UI\examples\Modal\InterruptiveItem\KeyValue;

/**
 * ---
 * description: >
 *   Example for rendering a key value interruptive item modal.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show an interruptive Item".
 *   A click onto the button will grey out ILIAS, open a modal with some content (e.g. "First Key", "first value"). The
 *   value is displayed differently to the label.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see some key-value interruptive items:';
    $modal = $factory->modal()->interruptive('My Title', $message, "#")
                     ->withAffectedItems(array(
                         $factory->modal()->interruptiveItem()->keyValue(
                             '10',
                             'First Key',
                             'first value'
                         ),
                         $factory->modal()->interruptiveItem()->keyValue(
                             '20',
                             'Second Key',
                             'second value'
                         ),
                         $factory->modal()->interruptiveItem()->keyValue(
                             '30',
                             'Third Key',
                             'really really long value, much much longer than any value needs to be, that should be cut off at some point'
                         )
                     ));
    $button = $factory->button()->standard('Show some key-value interruptive Items', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
