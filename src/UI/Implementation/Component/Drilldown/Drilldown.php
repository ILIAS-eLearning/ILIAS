<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Drilldown;

use ILIAS\UI\Component\Drilldown as IDrilldown;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Drilldown Control
 */
class Drilldown implements IDrilldown\Drilldown
{
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var \ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
	 */
	protected $icon_or_glyph;

	/**
	 * @var \ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
	 */
	protected $back_icon;

	/**
	 * @var int
	 */
	protected $stacking = 1;

	/**
	 * @var array
	 */
	protected $entries = [];

	public function __construct(string $label, $icon_or_glyph = null)
	{
		$this->label = $label;
		$this->icon_or_glyph = $icon_or_glyph;

		global $DIC;
		$f = $DIC['ui.factory']->drilldown();
		$this->self_entry = $f->level($label, $icon_or_glyph);
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function getIconOrGlyph()
	{
		return $this->icon_or_glyph;
	}

	/**
	 * @inheritdoc
	 */
	public function withAdditionalEntry($entry): IDrilldown\Drilldown
	{
		$classes = [IDrilldown\Level::class, \ILIAS\UI\Component\Button\Button::class];
		$check = [$entry];
		$this->checkArgListElements("entry", $check, $classes);

		$clone = clone $this;
		$clone->self_entry = $clone->self_entry->withAdditionalEntry($entry);
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getEntries(): array
	{
		return [$this->self_entry];
	}
}