<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Menu;

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
     *     Drilldown Menus consist of a list of Buttons organized in three areas:
     *     The backlink-area holds exactly one Button to navigate to a higher level of
     *     entries, the following area again holds exactly one button to outline
     *     the current position within the tree-structure and finally the main area
     *     consisting of an unlimited number of buttons.
     *     Also, Dividers may be used so separate entries from each other.
     *   effect: >
     *     Buttons within the Drilldown Menu will either affect the Menu itself or
     *     trigger other navigational events.
     *     Speaking of the the first ("Submenus"), the user will navigate down the
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
     * @param 	\ILIAS\UI\Component\Clickable | string		$label
     * @param 	array<\ILIAS\UI\Component\Menu\Sub | \ILIAS\UI\Component\Clickable | \ILIAS\UI\Component\Divider> $items
     * @return 	\ILIAS\UI\Component\Menu\Drilldown
     */
    public function drilldown($label, array $items) : Drilldown;


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
     * @param 	\ILIAS\UI\Component\Clickable | string		$label
     * @param 	array<\ILIAS\UI\Component\Menu\Sub | \ILIAS\UI\Component\Clickable | \ILIAS\UI\Component\Divider> $items
     * @return 	\ILIAS\UI\Component\Menu\Sub
     */
    public function sub($label, array $items) : Sub;
}
