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

namespace ILIAS\UI\examples\Card\RepositoryObject;

/**
 * ---
 * descriptions: >
 *   Example for rendering a repository card with an object icon and small progressmeter
 *
 * expected output: >
 *   ILIAS shows a base ILIAS-Logo. The logo's size changes accordingly to the size of the browser window/desktop.
 *   Additionally you can see a small progress meter next to the course icon.
 * ---
 */
function with_object_icon_and_progressmeter_mini()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Course');
    $progressmeter = $f->chart()->progressMeter()->mini(100, 70);

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
    )->withProgress(
        $progressmeter
    )->withSections(
        array(
            $content,
            $content,
        )
    );

    //Render
    return $renderer->render($card);
}
