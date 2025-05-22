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

namespace ILIAS\UI\examples\Panel\Listing\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a panel standard listing with an lead image.
 *
 * expected output: >
 *   ILIAS shows a panel including two item groups. The first item group includes two items, each displaying an action
 *   menu and a ILIAS-Logo as image. The second item group includes an action menu and a ILIAS-Logo.
 *   The logos are displayed on the left side of the text.
 * ---
 */
function with_lead_image()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $f->image()->responsive(
        "assets/ui-examples/images/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );
    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $f->item()->standard("ILIAS Beginner Course")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $list_item2 = $f->item()->standard("ILIAS Advanced Course")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $list_item3 = $f->item()->standard("ILIAS User Group")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $std_list = $f->panel()->listing()->standard("Content", array(
        $f->item()->group("Courses", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Groups", array(
            $list_item3
        ))
    ));


    return $renderer->render($std_list);
}
