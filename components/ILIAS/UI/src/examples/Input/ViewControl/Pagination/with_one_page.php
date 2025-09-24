<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Pagination;

use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

/**
 * ---
 * expected output: >
 *   No pagination component is rendered.
 * ---
 */
function with_one_page()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $pagination = $f->input()->viewControl()->pagination()
        ->withTotalCount(3)
        ->withValue([Pagination::FNAME_OFFSET => 0, Pagination::FNAME_LIMIT => 10])
    ;

    //view this in a ViewControlContainer with active request
    $vc_container = $f->input()->container()->viewControl()->standard([$pagination])
        ->withRequest($DIC->http()->request());

    return $r->render([
        $vc_container
    ]);
}
