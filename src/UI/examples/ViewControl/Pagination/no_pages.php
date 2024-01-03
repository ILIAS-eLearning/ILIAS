<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * A Pagination with one page only will render as empty string
 */
function no_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $pagination = $factory->viewControl()->pagination()
        ->withPageSize(10)
        ->withTotalEntries(10);

    return $renderer->render($pagination);
}
