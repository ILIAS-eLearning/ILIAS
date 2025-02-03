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

namespace ILIAS\UI\examples\Input\ViewControl\Mode;

/**
 * ---
 * description: >
 *   Base example of a Mode View Controls
 *
 * expected output: >
 *   Ilias renders a viewcontrol consisting of three buttons.
 *   When clicking a button, the page reloads and the clicked button is engaged.
 *   The value (m1, m2, x) is shown above.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $m = $f->input()->viewControl()->mode(
        [
            'm1' => 'some mode',
            'm2' => 'other mode',
            'x' => '...',
        ]
    );

    //it's more fun to view this in a ViewControlContainer
    $vc_container = $f->input()->container()->viewControl()->standard([$m])
        ->withRequest($DIC->http()->request());

    return $r->render([
        $f->legacy()->content('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
