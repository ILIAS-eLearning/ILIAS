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

namespace ILIAS\UI\examples\Modal\LightboxCardPage;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function show_modal_on_button_click(): string
{
    global $DIC;

    $ui = $DIC->ui()->factory();

    $item = $ui->item()
               ->standard('Some information')
               ->withDescription('A very long text.');


    $another_item = $ui->item()
                       ->standard('Some other information')
                       ->withDescription('Another very long text.')
                       ->withProperties([
                           'Belongs to' => 'No one',
                           'Created on' => 'June the 15th',
                           'Awarded by' => 'John Doe',
                           'Valid until' => 'Forever',
                       ]);

    $card = $ui->card()
               ->standard('A card title')
               ->withSections([$item])
               ->withHiddenSections([$another_item]);
    $box = $ui->modal()->lightboxCardPage($card);
    $modal = $ui->modal()->lightbox($box);

    $button = $ui
        ->button()
        ->standard('Show Modal', '')
        ->withOnClick($modal->getShowSignal());

    return $DIC->ui()->renderer()->render([$button, $modal]);
}
