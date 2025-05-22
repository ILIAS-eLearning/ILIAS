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

namespace ILIAS\UI\examples\Menu\Sub;

/**
 * ---
 * description: >
 *   Example for rendering a submenu.
 *
 * expected output: >
 *   The sub-menu is actually not meant to be rendered standalone.
 * ---
 */
function sub()
{
    $comment =
    '<p> The sub-menu is actually not meant to be rendered standalone. '
    . 'However, it will generate a ul-tree with buttons for nodes. See Drilldown for a Example using Sub Menus<p/>';

    return $comment;
}
