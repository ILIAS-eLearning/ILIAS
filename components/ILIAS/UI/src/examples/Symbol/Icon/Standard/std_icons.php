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

namespace ILIAS\UI\Examples\Symbol\Icon\Standard;

/**
 * ---
 * description: >
 *   Example for rendering standard icons.
 *
 * expected output: >
 *   ILIAS shows labels with an icon each. Please report missing icons which might get displayed as a black block or
 *   in a faulty prestentation as a bug including the identifier's name.
 * ---
 */
function std_icons()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $lng = $DIC->language();

    $i = $f->symbol()->icon()->standard('NONE', 'DummyIcon');
    $default_icons = $i->getAllStandardHandles();
    $buffer = array();

    foreach ($default_icons as $icon) {
        $i = $f->symbol()->icon()->standard($icon, $icon, 'medium');
        $buffer[] = $renderer->render($i)
        . ' '
        . $icon
        . ' - '
        . $lng->txt("obj_$icon");
    }

    return implode('<br><br>', $buffer);
}
