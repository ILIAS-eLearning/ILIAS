<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Sortation implements C\ViewControl\Sortation {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var string
	 */
	protected $identifier="sortation";

	/**
	 * @var string
	 */
	protected $active;

	/**
	 * @var arrary<string,string>
	 */
	protected $options=array();


	public function __construct(array $options) {
		$this->options = $options;
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
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function withIdentifier($identifier) {
		$this->checkStringArg("identifier", $identifier);
		$clone = clone $this;
		$clone->identifier = $identifier;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @inheritdoc
	 */
	public function getOptions() {
		return $this->options;
	}

}
