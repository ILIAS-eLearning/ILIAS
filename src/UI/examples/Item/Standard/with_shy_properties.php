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

namespace ILIAS\UI\examples\Item\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard item with shy properties.
 *
 * expected output: >
 *   ILIAS shows a box including the following informations: A heading with a dummy text in small writings
 *   ("Lorem ipsum...") below. Beneath those you can see a fine line and more informations about "LMS", "Code Repo"
 *   and "Location". "Code Repo" ("ILIAS") and "LMS" ("GitHub") are rendered as shy buttons and are functioning as links.
 * ---
 */
function with_shy_properties()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $app_item = $f->item()->standard("Item Title")
        ->withProperties(array(
            "LMS" => $f->button()->shy("ILIAS", "https://www.ilias.de"),
            "Code Repo" => $f->button()->shy("GitHub", "https://www.github.com"),
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
    return $renderer->render($app_item);
}
