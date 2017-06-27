<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements C\Input\Input {
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $byline;

	public function __construct($label, $byline) {
		$this->checkStringArg("label", $label);
		$this->checkStringArg("byline", $byline);
		$this->label = $label;
		$this->byline= $byline;
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function withLabel($label) {
		$this->checkStringArg("label", $label);
		$clone = clone $this;
		$clone->label = $label;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getByline() {
		return $this->byline;
	}

	/**
	 * @inheritdoc
	 */
	public function withByline($byline) {
		$this->checkStringArg("byline", $byline);
		$clone = clone $this;
		$clone->byline = $byline;
		return $clone;
	}
}
