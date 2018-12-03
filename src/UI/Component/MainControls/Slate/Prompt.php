<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

/**
 * Prompts are notifications from the system to the user.
 */
interface Prompt extends Slate
{
	/**
	 * Set the Prompt's Status Counter to $count.
	 */
	public function	withUpdatedStatusCounter(int $count): Prompt;

	/**
	 * Set the Prompt's Novelty Counter to $count.
	 */
	public function withUpdatedNoveltyCounter(int $count): Prompt;
}
