<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
        $f->legacy()->content('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
