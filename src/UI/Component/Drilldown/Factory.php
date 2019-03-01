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
	 * @return \ILIAS\UI\Component\Drilldown\Drilldown
	 */
	public function drilldown(string $label, $icon_or_glyph = null): Drilldown;

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
	 * @return 	\ILIAS\UI\Component\Drilldown\Level
	 */
	public function level(string $label, $icon_or_glyph = null): Level;
}