<?php
declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;

/**
 * Bulky Button
 */
class Bulky extends Button implements C\Button\Bulky
{
	use Engageable;

	/**
	 * @var 	ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
	 */
	protected $icon_or_glyph;

	public function __construct($icon_or_glyph, string $label, string $action)
	{
		$allowed_classes = [C\Icon\Icon::class, C\Glyph\Glyph::class];
		$graphical_param = array($icon_or_glyph);
		$this->checkArgListElements("icon_or_glyph", $graphical_param, $allowed_classes);
		$this->checkStringArg("label", $label);
		$this->checkStringArg("action", $action);
		$this->icon_or_glyph = $icon_or_glyph;
		$this->label = $label;
		$this->action = $action;
	}

	/**
	 * @inheritdoc
	 */
	public function getIconOrGlyph()
	{
		return $this->icon_or_glyph;
	}

}