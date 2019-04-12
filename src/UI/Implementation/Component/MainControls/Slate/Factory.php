<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Legacy\Legacy as ILegacy;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Counter\Factory as CounterFactory;

class Factory implements ISlate\Factory
{
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @var ILIAS\UI\Component\Counter\Factory
	 */
	protected $counter_factory;

	public function __construct(
		SignalGeneratorInterface $signal_generator,
		CounterFactory $counter_factory
	) {
		$this->signal_generator = $signal_generator;
		$this->counter_factory = $counter_factory;
	}

	public function legacy(string $name, $symbol, ILegacy $content): ISlate\Legacy
	{
		return new Legacy($this->signal_generator, $name, $symbol, $content);
	}

	public function combined(string $name, $symbol): ISlate\Combined
	{
		return new Combined($this->signal_generator, $name, $symbol);
	}
}
