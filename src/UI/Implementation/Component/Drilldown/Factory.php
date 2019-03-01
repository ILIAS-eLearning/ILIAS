<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Drilldown;

use ILIAS\UI\Component\Drilldown as IDrilldown;

class Factory implements IDrilldown\Factory
{
	/**
	 * @inheritdoc
	 */
	public function drilldown(string $label, $icon_or_glyph = null): IDrilldown\Drilldown
	{
		return new Drilldown($label, $icon_or_glyph);
	}

	/**
	 * @inheritdoc
	 */
	public function level(string $label, $icon_or_glyph = null): IDrilldown\Level
	{
		return new Level($label, $icon_or_glyph);
	}

}
