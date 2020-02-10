<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Control;

use ILIAS\UI\Component\Input\Control;

/**
 * Factory for Controls
 */
class Factory implements Control\Factory
{
	public function fieldSelection(
		array $options,
		string $label = Control\FieldSelection::DEFAULT_DROPDOWN_LABEL,
		string $button_label = Control\FieldSelection::DEFAULT_BUTTON_LABEL
	): Control\FieldSelection {
		throw new \ILIAS\UI\NotImplementedException();
	}
}
