<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Pagination;

function with_limit_options()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $dummy_signal = new \ILIAS\UI\Implementation\Component\Signal('');

    $pagination_with_value = $f->input()->viewControl()->pagination()
        ->withOnChange($dummy_signal)
        ->withTotalCount(6932)
        ->withValue(["offset" => 40, "limit" => 10]);

    $pagination_with_options = $f->input()->viewControl()->pagination()
        ->withOnChange($dummy_signal)
        ->withTotalCount(6932)
        ->withLimitOptions([10,100,500,1000]);

    $pagination_without_total = $f->input()->viewControl()->pagination()
        ->withOnChange($dummy_signal)
        ->withValue(["offset" => 42, "limit" => 10]);


    return $r->render([
        $pagination_with_value,
        $f->divider()->horizontal(),
        $pagination_with_options,
        $f->divider()->horizontal(),
        $pagination_without_total
    ]);
}
