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

namespace ILIAS\UI\examples\Button\Month;

/**
 * ---
 * description: >
 *   Example for rendering a dropdown button showing the default month/year while not opened and a selection of months while
 *   opened.
 *
 * expected output: >
 *   ILIAS shows a button including a month and year. Clicking the button will open a selection of other months and years
 *   which can be selected. Another click onto a month opens a dialog which confirms the click. In this dialog the selected
 *   month (e.g. 03-2020) is included.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->month("02-2017")->withOnLoadCode(function ($id) {
        return "$(\"#$id\").on('il.ui.button.month.changed', function(el, id, month) { alert(\"Clicked: \" + id + ' with ' + month);});";
    }));
}
