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
 *   Example for rendering a disabled standard icon.
 *
 * expected output: >
 *   ILIAS shows a standard icon in an alternative version. It's design hints to the icon being disabled.
 * ---
 */
function disabled_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->symbol()->icon()->standard('grp', 'Group', 'large', false);

    $buffer[] = $renderer->render($ico) . ' Large Group Enabled';
    $buffer[] = $renderer->render($ico->withDisabled(true)) . ' Large Group Disabled';

    return implode('<br><br>', $buffer);
}
