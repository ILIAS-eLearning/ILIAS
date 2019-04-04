<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;

class Factory implements IMenu\Factory
{
	/**
	 * @inheritdoc
	 */
	public function drilldown(string $label, $icon_or_glyph = null): IMenu\Drilldown
	{
		return new Drilldown($this, $label, $icon_or_glyph);
	}

	/**
	 * @inheritdoc
	 */
	public function sub(string $label, $icon_or_glyph = null): IMenu\Sub
	{
		return new Sub($label, $icon_or_glyph);
	}

}
