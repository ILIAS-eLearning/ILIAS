<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as Interface;

/**
 * Factory for View Controls
 */
class Factory implements ViewControl\Factory
{
	public function fieldSelection(
		array $options,
		string $label = Interface\FieldSelection::DEFAULT_DROPDOWN_LABEL,
		string $button_label = Interface\FieldSelection::DEFAULT_BUTTON_LABEL
	): Interface\FieldSelection {
		throw new \ILIAS\UI\NotImplementedException();
	}
}
