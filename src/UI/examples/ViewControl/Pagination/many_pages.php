<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * ---
 * description: >
 *   Example of rendering a pagination view control with a lot of pages
 *
 * expected output: >
 *   ILIAS shows a series of numbers 1-500 in between the "Back" (<) and "Next" (>) glyph. The series of numbers is not
 *   displayed completely as it is limited to six elements (e.g. 1, 2, 3, 4, 5, 500). Clicking a number loads a new page:
 *   ILIAS shows a updated number under the series of numbers, e.g. "entries 8 to 10" after clicking "5". You can browse
 *   through the pages with the glyphs too, but  please note that the "Back" glyph can't be used on the first page and
 *   the "Next" glyph can't be used on the last page.
 * ---
 */
function many_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $parameter_name = 'page2';
    $current_page = 0;
    if ($request_wrapper->has($parameter_name)) {
        $current_page = $request_wrapper->retrieve($parameter_name, $refinery->kindlyTo()->int());
    }

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(1000)
        ->withPageSize(2)
        ->withMaxPaginationButtons(5)
        ->withCurrentPage($current_page);

    list($range_offset, $range_length) = $pagination->getRange()->unpack();
    $result = "Show $range_length entries starting at $range_offset";

    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
