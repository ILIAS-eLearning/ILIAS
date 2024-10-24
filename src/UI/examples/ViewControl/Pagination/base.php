<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * ---
 * description: >
 *   Example for rendering a pagination view control.
 *
 * expected output: >
 *   ILIAS shows a series of numbers 1-10 positioned between a "Back" (<) and "Next" (<) glyph. Clicking a number loads a
 *   new page: the updated number will be shown under the series of numbers, e.g. "entries 80-90" after clicking "9". You
 *   can browse the pages through the glyphs too. Please note that the "Back" glyph can't be used on the first page and the
 *   "Next" glyph can't be used on the last page.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $parameter_name = 'page';
    $current_page = 0;
    if ($request_wrapper->has($parameter_name)) {
        $current_page = $request_wrapper->retrieve($parameter_name, $refinery->kindlyTo()->int());
    }

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(98)
        ->withPageSize(10)
        ->withCurrentPage($current_page);

    list($range_offset, $range_length) = $pagination->getRange()->unpack();
    $result = "Show $range_length entries starting at $range_offset";

    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
