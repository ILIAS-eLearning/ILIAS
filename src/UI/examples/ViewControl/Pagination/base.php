<?php

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $parameter_name = 'page';
    $current_page = (int) @$_GET[$parameter_name];

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(98)
        ->withPageSize(10)
        ->withCurrentPage($current_page);

    $start = $pagination->getOffset();
    $stop = $start + $pagination->getPageLength();
    $result = "entries $start to $stop";
    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
