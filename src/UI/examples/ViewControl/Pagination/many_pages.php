<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Pagination;

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
