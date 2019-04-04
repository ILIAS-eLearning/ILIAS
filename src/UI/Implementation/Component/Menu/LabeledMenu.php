<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Basic Menu Control with a Label
 */
abstract class LabeledMenu extends Menu implements IMenu\LabeledMenu
{
	/**
	 * @var Component | string
	 */
	protected $label;

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function withLabel($label): IMenu\LabeledMenu
	{
		$classes = [Component\Clickable::class, \string::class];
		$check = [$label];
		$this->checkArgListElements("label", $check, $classes);

		$clone = clone $this;
		$clone->label = $label;
		return $clone;
	}
}
