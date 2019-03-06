<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Drilldown;

use ILIAS\UI\Component\Drilldown as IDrilldown;

class Factory implements IDrilldown\Factory
{
	/**
	 * @inheritdoc
	 */
	public function menu(string $label, $icon_or_glyph = null): IDrilldown\Menu
	{
		return new Menu($label, $icon_or_glyph);
	}

	/**
	 * @inheritdoc
	 */
	public function submenu(string $label, $icon_or_glyph = null): IDrilldown\Submenu
	{
		return new Submenu($label, $icon_or_glyph);
	}

}
