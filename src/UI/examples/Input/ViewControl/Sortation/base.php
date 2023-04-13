<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Sortation;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    //construct with options and labels
    $sortation = $f->input()->viewControl()->sortation(
        [
            'field1:ASC' => 'Field 1, ascending',
            'field1:DESC' => 'Field 1, descending',
            'field2:ASC' => 'Field 2, ascending'
        ]
    );

    //wrap the control in a ViewControlContainer
    $vc_container = $f->input()->container()->viewControl()->standard([$sortation])
        ->withRequest($DIC->http()->request());

    return $r->render([$vc_container, $f->divider()->horizontal()]) . print_r($vc_container->getData(), true);
}
