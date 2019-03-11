<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Drilldown;

use ILIAS\UI\Component\Drilldown as IDrilldown;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Level of Drilldown Control
 */
class Submenu implements IDrilldown\Submenu
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
	 * @var array
	 */
	protected $entries = [];

	/**
	 * @var bool
	 */
	protected $active = false;

	public function __construct(string $label, $icon_or_glyph = null)
	{
		$this->label = $label;
		$this->icon_or_glyph = $icon_or_glyph;
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
	public function withEntries(array $entries): IDrilldown\Submenu
	{
		$classes = [IDrilldown\Submenu::class, \ILIAS\UI\Component\Button\Button::class];
		$this->checkArgListElements("entry", $entries, $classes);

		$clone = clone $this;
		$clone->entries = $entries;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withAdditionalEntry($entry): IDrilldown\Submenu
	{
		$classes = [IDrilldown\Submenu::class, \ILIAS\UI\Component\Button\Button::class];
		$check = [$entry];
		$this->checkArgListElements("entry", $check, $classes);

		$clone = clone $this;
		$clone->entries[] = $entry;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getEntries(): array
	{
		return $this->entries;
	}


	/**
	 * @inheritdoc
	 */
	public function withInitiallyActive(): IDrilldown\Submenu
	{
		$clone = clone $this;
		$clone->active = true;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function isInitiallyActive(): bool
	{
		return $this->active;
	}

}