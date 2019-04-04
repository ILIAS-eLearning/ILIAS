<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Level of Drilldown Control
 */
class Sub extends Menu implements IMenu\Sub
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
		if(! is_null($icon_or_glyph)) {
			$classes = [Icon::class, Glyph::class];
			$check = [$icon_or_glyph];
			$this->checkArgListElements("icon_or_glyph", $check, $classes);
		}

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
	public function withEntries(array $entries): IMenu\Sub
	{
		$classes = [IMenu\Sub::class, \ILIAS\UI\Component\Button\Button::class];
		$this->checkArgListElements("entry", $entries, $classes);

		$clone = clone $this;
		$clone->entries = $entries;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withAdditionalEntry($entry): IMenu\Sub
	{
		$classes = [IMenu\Sub::class, \ILIAS\UI\Component\Button\Button::class];
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
	public function withInitiallyActive(): IMenu\Sub
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