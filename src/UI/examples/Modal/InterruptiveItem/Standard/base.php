<?php

declare(strict_types=1);

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

namespace ILIAS\UI\examples\Modal\InterruptiveItem\Standard;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see a standard interruptive Item:';
    $icon = $factory->image()->standard('./templates/default/images/icon_crs.svg', '');
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
    $button = $factory->button()->standard('Show a standard interruptive Item', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
