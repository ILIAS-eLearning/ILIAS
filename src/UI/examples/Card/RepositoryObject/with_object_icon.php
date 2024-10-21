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
 * description: >
 *   Example for rendering a repository card with an object icon
 *
 * expected output: >
 *   ILIAS shows a ILIAS-Logo. The logo's size changes accordingly to the size of the browser window/desktop. A title and
 *   some entries (Entry 1, Some text etc.) are displayed below the logo. In the left top corner you can see the outlined
 *   icon "Course".
 * ---
 */
function with_object_icon()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Course');

    $image = $f->image()->responsive(
        "./templates/default/images/logo/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $content = $f->listing()->descriptive(
        array(
            "Entry 1" => "Some text",
            "Entry 2" => "Some more text",
        )
    );

    $card = $f->card()->repositoryObject(
        "Title",
        $image
    )->withObjectIcon(
        $icon
    )->withSections(
        array(
            $content,
            $content
        )
    );
    //Render
    return $renderer->render($card);
}
