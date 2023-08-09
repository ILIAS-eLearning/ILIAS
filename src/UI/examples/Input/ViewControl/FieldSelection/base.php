<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\FieldSelection;

/**
 * basic example of a FieldSelection ViewControl
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    //construct with options and labels for aria and button.
    $fs = $f->input()->viewControl()->fieldSelection(
        [
            'c1' => 'column 1',
            'c2' => 'column 2',
            'x' => '...'
        ],
        'shown columns',
        'apply'
    );

    //it's more fun to view this in a ViewControlContainer
    $vc_container = $f->input()->container()->viewControl()->standard([$fs])
        ->withRequest($DIC->http()->request());

    return $r->render([
        $f->legacy('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
