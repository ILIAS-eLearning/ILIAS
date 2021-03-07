<?php

function many_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $parameter_name = 'page2';
    $current_page = (int) (array_key_exists($parameter_name, $_GET) ? $_GET[$parameter_name] : 0);

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
