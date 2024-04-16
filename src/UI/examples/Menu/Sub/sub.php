<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Menu\Sub;

/**
 * ---
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
