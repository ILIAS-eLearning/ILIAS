<?php

function many_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $parameter_name = 'page2';
    $current_page = (int) @$_GET[$parameter_name];

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(1000)
        ->withPageSize(2)
        ->withMaxPaginationButtons(5)
        ->withCurrentPage($current_page);

    $start = $pagination->getOffset();
    $stop = $start + $pagination->getPageLength();
    $result = "entries $start to $stop";
    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
