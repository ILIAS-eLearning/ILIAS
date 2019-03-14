<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Button\Bulky as IBulky;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Combined Slate
 */
class Combined extends Slate implements ISlate\Combined
{
	/**
	 * @var array<Slate|Bulky>
	 */
	protected $contents = [];

	public function __construct(
		SignalGeneratorInterface $signal_generator,
		string $name,
		$symbol
	) {
		parent::__construct($signal_generator, $name, $symbol);
	}

	/**
	 * @inheritdoc
	 */
	public function withAdditionalEntry($entry): ISlate\Combined
	{
		$classes = [IBulky::class, ISlate\Slate::class];
		$check = [$entry];
		$this->checkArgListElements("Slate or Bulky-Button", $check, $classes);

		$clone = clone $this;
		$clone->contents[] = $entry;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getContents(): array
	{
		return $this->contents;
	}
}
