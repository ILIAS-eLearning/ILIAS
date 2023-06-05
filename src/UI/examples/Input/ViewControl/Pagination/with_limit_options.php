<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Pagination;

function with_limit_options()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $pagination_with_value = $f->input()->viewControl()->pagination()
        ->withTotalCount(6932)
        ->withValue('40:10');

    $pagination_with_options = $f->input()->viewControl()->pagination()
        ->withTotalCount(6932)
        ->withLimitOptions([10,100,500,1000]);

    $pagination_without_total = $f->input()->viewControl()->pagination()
        ->withValue('42:10');


    return $r->render([
        $pagination_with_value,
        $f->divider()->horizontal(),
        $pagination_with_options,
        $f->divider()->horizontal(),
        $pagination_without_total
    ]);
}
