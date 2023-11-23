<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Pagination;

use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $pagination = $f->input()->viewControl()->pagination()
        ->withTotalCount(932)
        ->withValue([Pagination::FNAME_OFFSET => 31, Pagination::FNAME_LIMIT => 10])
    ;

    //view this in a ViewControlContainer with active request
    $vc_container = $f->input()->container()->viewControl()->standard([$pagination])
        ->withRequest($DIC->http()->request());

    return $r->render([
        $f->legacy('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
