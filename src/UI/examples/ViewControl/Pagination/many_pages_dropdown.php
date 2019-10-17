<?php

function many_pages_dropdown()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $parameter_name = 'page2';
    $current_page = (int) @$_GET[$parameter_name];

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(102)
        ->withPageSize(10)
        ->withDropdownAt(5)
        ->withCurrentPage($current_page);

    $start = $pagination->getOffset();
    $stop = $start + $pagination->getPageLength();
    $result = "entries $start to $stop";
    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
