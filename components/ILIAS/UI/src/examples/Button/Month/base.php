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
 *   ILIAS shows a month and year.
 *   On browsers that support type='month' inputs like Chrome or Safari on iPhone and iPad: Clicking the button will
 *   open a picker for other months and years which can be selected. Another click onto a month opens a dialog which
 *   confirms the click. In this dialog the selected month (e.g. 03-2020) is mentioned.
 *   On other browsers like Firefox and Safari on desktop: There might be a flicker while the browser loads the fallback
 *   solution. Then, the month can be changed through a select input dropdown and the year can be changed through a
 *   text input. On a valid input, a dialog will appear and mention the selected month (e.g. 03-2020). On an invalid
 *   year input (e.g. one or three digits), the year text field will be highlighted as invalid. A two-digit year input
 *   e.g.'24' will be converted to a four-digit year e.g. '2024'.
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
