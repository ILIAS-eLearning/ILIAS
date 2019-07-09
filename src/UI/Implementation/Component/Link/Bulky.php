<?php

declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;

class Bulky extends Link implements C\Link\Bulky {

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var Symbol
	 */
	protected $symbol;

	function __construct(C\Symbol\Symbol $symbol, string $label, string $action)
	{
		parent::__construct($action);
		$this->label = $label;
		$this->symbol = $symbol;
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
	public function getSymbol(): C\Symbol\Symbol
	{
		return $this->symbol;
	}
}
