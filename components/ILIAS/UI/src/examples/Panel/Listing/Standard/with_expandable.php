<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Listing\Standard;

function with_expandable(): string
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

    $current_page = 0;
    if ($request_wrapper->has('page')) {
        $current_page = $request_wrapper->retrieve('page', $refinery->kindlyTo()->int());
    }
    $pagination = $f->viewControl()->pagination()
                    ->withTargetURL($url, "page")
                    ->withTotalEntries(98)
                    ->withPageSize(10)
                    ->withMaxPaginationButtons(1)
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

    $std_list = $f->panel()->listing()->standard("List Title", [
        $f->item()->group("Subtitle 1", [
            $item1,
            $item2
        ]),
        $f->item()->group("Subtitle 2", [
            $item3
        ])
    ])->withActions($actions)
                  ->withViewControls($view_controls)
                  ->withExpandable(false);

    return $renderer->render($std_list);
}
