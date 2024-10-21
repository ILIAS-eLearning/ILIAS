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
 *   Example for rendering a standard panel with an all view control.
 *
 * expected output: >
 *   ILIAS shows a panel with a large title "Panel Title", a text "Some Content" and a menu displayed by
 *   an triangle symbol pointing down. It also includes two subtitles and three items.
 *   On the left next to the menu a pagination field is displayed. The counter jumps back/forth if you click the <> symbols.
 *   Clicking the number will select those.
 *   On the left next to the pagination field a format selection field is displayed. Selection "Tile View" will change the
 *   display of the content to tiles. The standard text listing will be activated if you select "List View".
 *   On the left next to the format selection field a sortation button is displayed. Here you can sort the content of the panel.
 *   Selection a sortation will reload the page. The content won't be changed.
 * ---
 */
function with_all_view_controls(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $DIC->http()->request()->getRequestTarget();

    $actions = $f->dropdown()->standard([
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ]);
    $current_sortation = 'abc';
    if ($request_wrapper->has('sort')) {
        $current_sortation = $request_wrapper->retrieve('sort', $refinery->kindlyTo()->string());
    }

    $sortation_options = [
        'abc' => 'Sort by Alphabet',
        'date' => 'Sort by Date',
        'location' => 'Sort by Location'
    ];
    $sortation = $f->viewControl()->sortation($sortation_options)
                   ->withTargetURL($url, "sort")
                   ->withLabel($sortation_options[$current_sortation]);

    $current_presentation = 'list';
    if ($request_wrapper->has('mode')) {
        $current_presentation = $request_wrapper->retrieve('mode', $refinery->kindlyTo()->string());
    }
    $presentation_options = [
        'list' => 'List View',
        'tile' => 'Tile View'
    ];
    $modes = $f->viewControl()->mode(
        array_reduce(
            array_keys($presentation_options),
            static function ($carry, $item) use ($presentation_options, $url) {
                $carry[$presentation_options[$item]] = "$url&mode=$item";
                return $carry;
            },
            []
        ),
        'Presentation Mode'
    )
               ->withActive($presentation_options[$current_presentation]);

    $current_page = 0;
    if ($request_wrapper->has('page')) {
        $current_page = $request_wrapper->retrieve('page', $refinery->kindlyTo()->int());
    }
    $pagination = $f->viewControl()->pagination()
                    ->withTargetURL($url, "page")
                    ->withTotalEntries(98)
                    ->withPageSize(10)
                    ->withCurrentPage($current_page);

    $view_controls = [
        $sortation,
        $modes,
        $pagination
    ];

    if ($current_presentation === 'list') {
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
    } else {
        $image = $f->image()->responsive(
            "./templates/default/images/logo/HeaderIcon.svg",
            "Thumbnail Example"
        );
        $content = $f->listing()->descriptive(
            [
                "Entry 1" => "Some text",
                "Entry 2" => "Some more text",
            ]
        );
        $item1 = $item2 = $item3 = $f->card()->standard("Item Title", $image)
                                     ->withSections([$content]);
    }

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
                  ->withViewControls($view_controls);

    return $renderer->render($std_list);
}
