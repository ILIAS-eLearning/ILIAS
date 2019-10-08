<?php

function many_pages_dropdown()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();
    $lng = $DIC['lng'];

    $parameter_name = 'page2';
    $current_page = (int) @$_GET[$parameter_name];

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(102)
        ->withPageSize(10)
        ->withDropdownAt(5)
        ->withCurrentPage($current_page);

    $custom_pagination = $pagination
        ->withDropdownLabel('current page is %1$d (of %2$d pages in total)');

    $start = $pagination->getOffset();
    $stop = $start + $pagination->getPageLength();
    $result = "entries $start to $stop";
    return $renderer->render([$pagination, $custom_pagination])
        . '<hr>'
        . $result;
}
