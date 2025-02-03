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
 *   Example for rendering a panel standard listing with a lead text.
 *
 * expected output: >
 *   ILIAS shows a panel including two item groups. The first item group includes two items, each displaying an action
 *   menu and a lead text with periods of time. The second item group includes an action menu and a lead text with
 *   periods of time. Additionally each item is highlighted with colored bar.
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

    $df = new \ILIAS\Data\Factory();

    $list_item1 = $f->item()->standard("Weekly Meeting")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withColor($df->color('#ff00ff'))
        ->withLeadText("11:20 - 12:40");

    $list_item2 = $f->item()->standard("Tech VC")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withColor($df->color('#F9F9D0'))
        ->withLeadText("13:00 - 14:00");

    $list_item3 = $f->item()->standard("Jour Fixe")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withColor($df->color('#000000'))
        ->withLeadText("8:00 - 10:00");

    $std_list = $f->panel()->listing()->standard("Upcoming Events", array(
        $f->item()->group("Today", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Tomorrow", array(
            $list_item3
        ))
    ));


    return $renderer->render($std_list);
}
