<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Counter\Counter;

/**
 * Prompts are notifications from the system to the user.
 */
abstract class Prompt extends Slate implements ISlate\Prompt
{
	protected function getUIFactory(): Factory
	{
		global $DIC;
		return $DIC['ui.factory'];
	}

	protected function updateCounter(Counter $counter): ISlate\Prompt
	{
		$clone = clone $this;
		$clone->symbol = $clone->symbol->withCounter($counter);
		return $clone;
	}

	public function	withUpdatedStatusCounter(int $count): ISlate\Prompt
	{
		$counter = $this->getUIFactory()->counter()->status($count);
		return $this->updateCounter($counter);
	}

	public function withUpdatedNoveltyCounter(int $count): ISlate\Prompt
	{
		$counter = $this->getUIFactory()->counter()->novelty($count);
		return $this->updateCounter($counter);
	}
}