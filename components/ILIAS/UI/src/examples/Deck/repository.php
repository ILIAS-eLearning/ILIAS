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

namespace ILIAS\UI\examples\Deck;

/**
 * ---
 * description: >
 *   Example for rendering a repository card
 *
 * expected output: >
 *   ILIAS shows nine base cards. Additionally every single card includes outlined icons, certificate
 *   glyphs and action menus. You can open the menus via click.
 * ---
 */
function repository()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard('crs', 'Course');

    $items = array(
        $f->button()->shy("Go to Course", "#"),
        $f->button()->shy("Go to Portfolio", "#"),
        $f->divider()->horizontal(),
        $f->button()->shy("ilias.de", "http://www.ilias.de")
    );

    $dropdown = $f->dropdown()->standard($items);


    $content = $f->listing()->descriptive(
        array(
            "Entry 1" => "Some text",
            "Entry 2" => "Some more text",
        )
    );

    $image = $f->image()->responsive(
        "./assets/images/logo/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $card = $f->card()->repositoryObject(
        "Title",
        $image
    )->withObjectIcon(
        $icon
    )->withActions(
        $dropdown
    )->withCertificateIcon(
        true
    )->withSections(
        array(
            $content,
            $content,
        )
    );

    //Define the deck
    $deck = $f->deck(array($card,$card,$card,$card,$card,
        $card,$card,$card,$card))->withNormalCardsSize();

    //Render
    return $renderer->render($deck);
}
