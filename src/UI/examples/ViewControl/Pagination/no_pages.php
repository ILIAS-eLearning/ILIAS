<?php declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Pagination;

function no_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $pagination = $factory->viewControl()->pagination()
        ->withPageSize(10)
        ->withTotalEntries(0);

    $pagination_onepage = $pagination->withTotalEntries(9);
    $pagination_limited = $pagination->withMaxPaginationButtons(5);

    return $renderer->render($pagination)
        . '<hr>'
        . $renderer->render($pagination_onepage)
        . '<hr>'
        . $renderer->render($pagination_limited)
    ;
}
