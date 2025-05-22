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

namespace ILIAS\UI\examples\Popover\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard popover.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Card".
 *   A click onto the button opens a card popover.
 *   The popover can be closed by clicking onto the ILIAS background outside of the popover.
 * ---
 */
function show_card_in_popover()
{
    global $DIC;

    // This example shows how to render a card containing an image and a descriptive list inside a popover.
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $factory->image()->responsive("./assets/images/logo/HeaderIcon.svg", "Thumbnail Example");
    $card = $factory->card()->standard("Title", $image)->withSections(array($factory->legacy()->content("Hello World, I'm a card")));
    $popover = $factory->popover()->standard($card)->withTitle('Card');
    $button = $factory->button()->standard('Show Card', '#')
        ->withOnClick($popover->getShowSignal());

    return $renderer->render([$popover, $button]);
}
