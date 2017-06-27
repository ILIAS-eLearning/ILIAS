<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements C\Input\Input {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $byline;

	/**
	 * @var	string
	 */
	protected $name;

	public function __construct($label, $byline) {
		$this->checkStringArg("label", $label);
		$this->checkStringArg("byline", $byline);
		$this->label = $label;
		$this->byline= $byline;
		$this->name = null;
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

	/**
	 * The name of the input as used in HTML.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get an input like this one, with a different name.
	 *
	 * @param	string
	 * @return	Input
	 */
	public function withName($name) {
		$this->checkStringArg("name", $name);
		$clone = clone $this;
		$clone->name = $name;
		return $clone;
	}
}
