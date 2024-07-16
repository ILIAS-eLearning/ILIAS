<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\NullControl;

/**
 * ---
 * expected output: >
 *   ILIAS shows the results of the request, which is an array with one empty entry.
 *   No visible component is rendered below the divider.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $control = $f->input()->viewControl()->NullControl();

    //wrap the control in a ViewControlContainer
    $vc_container = $f->input()->container()->viewControl()->standard([$control])
        ->withRequest($DIC->http()->request());

    return $r->render([
        $f->legacy('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
