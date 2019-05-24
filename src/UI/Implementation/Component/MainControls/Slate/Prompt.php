<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Counter\Factory as CounterFactory;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Prompts are notifications from the system to the user.
 */
abstract class Prompt extends Slate implements ISlate\Prompt
{
	/**
	 * @var ILIAS\UI\Component\Counter\Factory
	 */
	protected $counter_factory;

	public function __construct(
		SignalGeneratorInterface $signal_generator,
		CounterFactory $counter_factory,
		string $name,
		Glyph $symbol
	) {
		$this->counter_factory = $counter_factory;
		parent::__construct($signal_generator, $name, $symbol);
	}

	protected function getCounterFactory(): CounterFactory
	{
		return $this->counter_factory;
	}

	protected function updateCounter(Counter $counter): ISlate\Prompt
	{
		$clone = clone $this;
		$clone->symbol = $clone->symbol->withCounter($counter);
		return $clone;
	}

	public function	withUpdatedStatusCounter(int $count): ISlate\Prompt
	{
		$counter = $this->getCounterFactory()->status($count);
		return $this->updateCounter($counter);
	}

	public function withUpdatedNoveltyCounter(int $count): ISlate\Prompt
	{
		$counter = $this->getCounterFactory()->novelty($count);
		return $this->updateCounter($counter);
	}
}