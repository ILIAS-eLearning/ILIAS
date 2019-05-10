<?php
declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Symbol\Icon\Icon as Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph as Glyph;

/**
 * Bulky Button
 */
class Bulky extends Button implements C\Button\Bulky
{
	use Engageable;

	/**
	 * @var 	ILIAS\UI\Component\Symbol\Icon\Icon | \ILIAS\UI\Component\Symbol\Glyph\Glyph
	 */
	protected $icon_or_glyph;

	public function __construct($icon_or_glyph, string $label, string $action)
	{
		$allowed_classes = [Icon::class, Glyph::class];
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