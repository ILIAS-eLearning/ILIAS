<?php
declare(strict_types=1);
namespace ILIAS\UI\examples\ViewControl\Pagination;

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

    list($range_offset, $range_length) = $pagination->getRange()->unpack();
    $result = "Show $range_length entries starting at $range_offset";

    return $renderer->render($pagination)
        . '<hr>'
        . $result;
}
