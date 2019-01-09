<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Legacy Slate
 */
class Legacy extends Slate implements ISlate\Legacy
{
	/**
	 * @var Component[]
	 */
	protected $contents = [];

	public function __construct(
		SignalGeneratorInterface $signal_generator,
		string $name,
		$symbol,
		string $contents
	) {
		parent::__construct($signal_generator, $name, $symbol);
		$this->contents = $this->transformToLegacyComponent($contents);
	}


	protected function transformToLegacyComponent(string $contents): \ILIAS\UI\Component\Legacy\Legacy
	{
		global $DIC;
		$factory = $DIC['ui.factory'];
		return $factory->legacy($contents);
	}

	public function getContents(): array
	{
		return [$this->contents];
	}

}
