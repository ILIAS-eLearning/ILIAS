<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Drilldown;

/**
 * Drilldown Factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A Drilldown Menu offers a partial view on a larger set of hierarchically
	 *     structured navigation possibilities.
	 *     While the entries of a Drilldown Menu are actually organised in a tree-structure,
	 *     there is only one level of branches visible at a time, so that space is
	 *     saved and the users attention is not being obstrused by irrelevant options.
	 *   composition: >
	 *     Drilldown Menus consist of a list of Buttons organized in three areas:
	 *     The backlink-area holds exactly one Button to navigate to a higher level of
	 *     entries, the following area again holds exactly one button to outline
	 *     the current position within the tree-structure and finally the main area
	 *     consisting of an unlimited number of buttons.
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
	 * @param 	string $label
	 * @param 	\ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph		$icon_or_glyph
	 * @return \ILIAS\UI\Component\Drilldown\Menu
	 */
	public function menu(string $label, $icon_or_glyph = null): Menu;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A Drilldown Menu offers navigational options to the user by
	 *     organizing them in a hierarchical structure. The Submenu is an
	 *     entry for the Drilldown Menu demarking a further level within the
	 *     hierarchy.
	 *   composition: >
	 *     A Submenu will be rendered as a Button within a Drilldown Menu.
	 *     It holds further Submenus and/or Buttons.
	 *   effect: >
	 *     Clicking the Button will show the flat list of Entries of this Submenu.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *          A Submenu MUST be used to generate a new level in the structure of
	 *          a Drilldown Menu.
	 *      2: >
	 *          Submenus MUST contain further Submenus or Buttons.
	 *      3: >
	 *          Submenus SHOULD contain more than one entry (Submenu or Button).
     *
	 *   wording:
	 *      1: >
	 *          Label and Symbol of the Submenu MUST reflect/subsume the meaning
	 *          or purpuse of contained entries.
	 *
	 * ---
	 * @param 	string $label
	 * @param 	\ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph		$icon_or_glyph
	 * @return 	\ILIAS\UI\Component\Drilldown\Submenu
	 */
	public function submenu(string $label, $icon_or_glyph = null): Submenu;
}