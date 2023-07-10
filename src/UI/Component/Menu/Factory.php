<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Menu;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Divider;

/**
 * Tree factory
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     A Drilldown Menu offers a partial view on a larger set of hierarchically
     *     structured navigation possibilities.
     *     While the entries of a Drilldown Menu are actually organized in a tree-structure,
     *     there is only one level of branches visible at a time, so that space is
     *     saved and the users attention is not being obstrused by irrelevant options.
     *   composition: >
     *     Drilldown Menus are rendered as a ul-list; an entry contains
     *     either a button plus a further list for a menu level
     *     or a list of buttons or links as leafs.
     *     Also, Dividers may be used so separate entries from each other.
     *     In the header-section of the menu the currently selected level is shown as headline,
     *     and a bulky button offers navigation to an upper level.
     *   effect: >
     *     Buttons within the Drilldown Menu will either affect the Menu itself or
     *     trigger other navigational events.
     *     Speaking of the first ("Submenus"), the user will navigate down the
     *     tree-structure of the Menu's entries. The currently selected level will be outlined,
     *     and a backlink will be presented to navigate back up the hierarchy.
     *     Entries directly below the current level will be presented as a flat list.
     * context:
     *   - Drilldown Menus are primarily used in Mainbar-Slates to break down navigational topics into smaller parts.
     *
     * rules:
     *   usage:
     *      1: >
     *          A Drilldown Menu MUST contain further Submenus or Buttons.
     *      2: >
     *          Drilldown Menus MUST contain more than one entry (Submenu or Button).
     *
     * ---
     * @param 	string $label
     * @param 	array<Component\Menu\Sub | Component\Clickable| Divider\Horizontal> $items
     * @return \ILIAS\UI\Component\Menu\Drilldown
     */
    public function drilldown(string $label, array $items): Drilldown;

    /**
     * ---
     * description:
     *   purpose: >
     *     Menus offer navigational options to the user. Sometimes, those options
     *     are organized in a hierarchical structure. The Submenu is an entry for
     *     Menus demarking a further level within this hierarchy.
     *   composition: >
     *     A Submenu is a derivate of Menu and will be rendered alike.
     *     It holds further Submenus and/or Buttons.
     *     Also, Dividers may be used so separate entries from each other.
     *   effect: >
     *     Clicking the Label of the Submenu will show the list of Entries of this Submenu.
     *
     * rules:
     *   usage:
     *      1: >
     *          A Submenu MUST be used to generate a new level in the structure of
     *          a Menu.
     *      2: >
     *          Submenus MUST contain further Submenus or Buttons.
     *      3: >
     *          Submenus SHOULD contain more than one entry (Submenu or Button).
     *
     *   wording:
     *      1: >
     *          Label and Symbol of the Submenu MUST reflect/subsume the meaning
     *          or purpose of contained entries.
     *
     * ---
     * @param 	array<Component\Menu\Sub | Component\Clickable| Divider\Horizontal> $items
     * @return 	\ILIAS\UI\Component\Menu\Sub
     */
    public function sub(string $label, array $items): Sub;
}
