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

namespace ILIAS\UI\examples\Panel\Standard;

/**
 * ---
 * description: >
 *   Example for rendering an expandable standard panel with view controls and actions.
 *
 * expected output: >
 *   ILIAS shows a standard panel with a collapsible heading, pagination view control, actions dropdown,
 *   and grouped item content that can be expanded or collapsed.
 * ---
 */
function with_expandable(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();

    $url = $DIC->http()->request()->getRequestTarget();

    $actions = $f->dropdown()->standard([
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ]);

    $current_page = 0;
    if ($request_wrapper->has('page')) {
        $current_page = $request_wrapper->retrieve('page', $refinery->kindlyTo()->int());
    }
    $pagination = $f->viewControl()->pagination()
                    ->withTargetURL($url, "page")
                    ->withTotalEntries(98)
                    ->withPageSize(10)
                    ->withCurrentPage($current_page);

    $view_controls = [$pagination];

    $item1 = $f->item()->standard("Item Title")
               ->withActions($actions)
               ->withProperties([
                   "Origin" => "Course Title 1",
                   "Last Update" => "24.11.2011",
                   "Location" => "Room 123, Main Street 44, 3012 Bern"
               ])
               ->withDescription(
                   "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua."
               );

    $item2 = $f->item()->standard("Item 2 Title")
               ->withActions($actions)
               ->withDescription(
                   "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua."
               );

    $item3 = $f->item()->standard("Item 3 Title")
               ->withActions($actions)
               ->withDescription(
                   "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua."
               );

    $expand_action = $data_factory->uri(
        $DIC->http()->request()->getUri()->__toString() . "&expand_action=1"
    );
    $collapse_action = $data_factory->uri(
        $DIC->http()->request()->getUri()->__toString() . "&collapse_action=1"
    );

    $std_list = $f->panel()->standard("List Title", [
        $f->item()->group("Subtitle 1", [
            $item1,
            $item2
        ]),
        $f->item()->group("Subtitle 2", [
            $item3
        ])
    ])
                  ->withActions($actions)
                  ->withViewControls($view_controls)
                  ->withExpandable(true, true, $expand_action, $collapse_action);

    return $renderer->render($std_list);
}
