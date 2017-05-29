<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\DropDown;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Drop down item
 */
class DropDownItem implements C\DropDown\DropDownItem {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $action;

	public function __construct($label, $action) {
		$this->checkStringArg("label", $label);
		$this->checkStringArg("action", $action);
		$this->label = $label;
		$this->action = $action;
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
	public function getAction() {
		return $this->action;
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
