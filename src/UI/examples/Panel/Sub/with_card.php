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

namespace ILIAS\UI\Examples\Panel\Sub;

/**
 * ---
 * description: >
 *   Example for rendering a sub panel with a card.
 *
 * expected output: >
 *   ILIAS shows a Panel including a large title "Panel Title" and a sub panel as content. The sub panel is titled
 *   "Sub Panel Title" and owns a text content "Some Content". Additionally it displays a card titled "Card Heading" and
 *   including the content "Card Content". On bigger desktops the card is displayed on the right side. On smaller desktops
 *   the card is displayed below the sub panel text content.
 * ---
 */
function with_card()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $block = $f->panel()->standard(
        "Panel Title",
        $f->panel()->sub("Sub Panel Title", $f->legacy("Some Content"))
            ->withFurtherInformation($f->card()->standard("Card Heading")->withSections(array($f->legacy("Card Content"))))
    );

    return $renderer->render($block);
}
