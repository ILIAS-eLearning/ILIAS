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
class Level implements IDrilldown\Level
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
	public function withAdditionalEntry($entry): IDrilldown\Level
	{
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

}