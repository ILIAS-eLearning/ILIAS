<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\DropDown;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between different types of drop downs.
 */
abstract class DropDown implements C\DropDown\DropDown {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $label;

	public function __construct($label) {
		$this->checkStringArg("label", $label);
		$this->label = $label;
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
	public function isActive() {
		return $this->active;
	}

	/**
	 * @inheritdoc
	 */
	public function withOnClick(Signal $signal) {
		return $this->addTriggeredSignal($signal, 'click');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnClick(Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'click');
	}

	/**
	 * @inheritdoc
	 */
	public function withOnHover(Signal $signal) {
		return $this->addTriggeredSignal($signal, 'hover');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnHover(Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'hover');
	}
}
