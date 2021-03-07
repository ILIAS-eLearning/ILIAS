<?php

function many_pages_dropdown()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();
    $lng = $DIC['lng'];

    $parameter_name = 'page3';
    $current_page = (int) (array_key_exists($parameter_name, $_GET) ? $_GET[$parameter_name] : 0);

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(102)
        ->withPageSize(10)
        ->withDropdownAt(5)
        ->withCurrentPage($current_page);

    $custom_pagination = $pagination
        ->withDropdownLabel('current page is %1$d (of %2$d pages in total)');

    list($range_offset, $range_length) = $pagination->getRange()->unpack();
    $result = "Show $range_length entries starting at $range_offset";

    return $renderer->render([$pagination, $custom_pagination])
        . '<hr>'
        . $result;
}
