<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Pagination;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    //construct without options; default value is 'unlimited'.
    $pagination = $f->input()->viewControl()->pagination();

    //view this in a ViewControlContainer with active request
    $vc_container = $f->input()->container()->viewControl()->standard([$pagination])
        ->withRequest($DIC->http()->request());

    return $r->render([$vc_container, $f->divider()->horizontal()]) . print_r($vc_container->getData(), true);
}
