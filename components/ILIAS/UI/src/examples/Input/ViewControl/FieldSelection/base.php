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

namespace ILIAS\UI\examples\Input\ViewControl\FieldSelection;

/**
 * ---
 * expected output: >
 *   There's a button with the column selection glyph as a label.
 *   Clicking the button will open a dropdown with three checkboxes and
 *   a standard button "apply".
 *   Tick one, many or no box and click "apply".
 *   The results will show the selected options.
 * ---
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
        $f->legacy()->content('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
