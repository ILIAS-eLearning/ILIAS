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

namespace ILIAS\UI\Component\Menu\Drilldown;

/**
 * Drilldown Factory.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Standard Drilldown Menu is the right choice for most contexts. The
     *     Standard Drilldown Menu shows always only one level of branches.
     * context:
     *   - Standard Drilldown Menus are primarily used in Mainbar-Slates to break
     *     down navigational topics into smaller parts.
     * ---
     * @param 	string $label
     * @param 	array<Component\Menu\Sub | Component\Clickable| Divider\Horizontal> $items
     * @return \ILIAS\UI\Component\Menu\Drilldown
     */
    public function standard(string $label, array $items): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Categorised Items Drilldown Menu is used to present a list of
     *     items sorted into categories for selection.
     *     The Catogrised Items Drilldown will always show the categories (root
     *     level of the tree) plus one level of subnodes if
     *     not presented on a small screen. On small screens it behaves like a
     *     Standard Drilldown Menu.
     *   composition: >
     *     Categorised Items Drilldown Menus can have a title or a filter, but
     *     not both.
     *   effect: >
     *     The Buttons for the categories will affect the Menu itself while the
     *     buttons for the title will trigger other navigational events.
     *
     * rules:
     *   usage:
     *      1: >
     *          A Catgorised Items Drilldown Menu MUST contain at least two levels
     *          of sub nodes: A list of categories containing at least one level
     *          of items.
     *
     * ---
     * @param 	string $label
     * @param 	array<Component\Menu\Sub> $items
     * @return \ILIAS\UI\Component\Menu\Drilldown
     */
    public function categorisedItems(string $label, array $items): CategorisedItems;
}
