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
 * expected output: >
 *   ILIAS shows a standard panel including a sub panel. Additionally a seconday panel is rendered in the right side
 *   of the sub panel content area.
 * ---
 */
function with_secondary_panel()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $f->item()->standard("Item Title")
                          ->withActions($actions)
                          ->withProperties(array(
                              "Origin" => "Course Title 1",
                              "Last Update" => "24.11.2011",
                              "Location" => "Room 123, Main Street 44, 3012 Bern"))
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item2 = $f->item()->standard("Item 2 Title")
                          ->withActions($actions)
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item3 = $f->item()->standard("Item 3 Title")
                          ->withActions($actions)
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $items = array(
        $f->item()->group("Listing Subtitle 1", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Listing Subtitle 2", array(
            $list_item3
        )));

    $panel = $f->panel()->secondary()->listing("Listing panel Title", $items)->withActions($actions);

    $block = $f->panel()->standard(
        "Panel Title",
        $f->panel()->sub("Sub Panel Title", $f->legacy()->content("Some Content"))
          ->withFurtherInformation($panel)
    );

    return $renderer->render($block);
}
