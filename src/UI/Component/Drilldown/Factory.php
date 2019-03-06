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
	 *     XX
	 *   composition: >
	 *
	 * rules:
	 *   usage:
	 *      1: X
	 *   accessibility:
	 *      1: X
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
	 *     XX
	 *   composition: >
	 *
	 * rules:
	 *   usage:
	 *      1: X
	 *   accessibility:
	 *      1: X
	 *
	 * ---
	 * @param 	string $label
	 * @param 	\ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph		$icon_or_glyph
	 * @return 	\ILIAS\UI\Component\Drilldown\Submenu
	 */
	public function submenu(string $label, $icon_or_glyph = null): Submenu;
}