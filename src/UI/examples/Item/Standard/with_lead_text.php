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
 *   Example for rendering a standard item with an lead text.
 *
 * expected output: >
 *   ILIAS shows a box including the following informations: A heading with a dummy text in small writings
 *   ("Lorem ipsum...") below. Beneath those you can see a fine line and more informations about "Origin", "Last Update"
 *   and "Location". Additionally a action menu is displayed in the box on the right top. On the left side a period of time
 *   (11:20-12:40) is displayed. On small screens the period of time will be rendered above the title.
 * ---
 */
function with_lead_text()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));
    $app_item = $f->item()->standard("Weekly Meeting")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadText("11:20 - 12:40");
    return $renderer->render($app_item);
}
