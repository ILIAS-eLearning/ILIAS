<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\TriggeredSignal;

class Toggle extends Button implements C\Button\Toggle {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var bool
	 */
	protected $is_on;

	/**
	 * @var string|null
	 */
	protected $action_off;

	protected $action_off_signal;
	protected $action_on_signal;

	public function __construct($label, $action, $action_off, $is_on)
	{
		$this->checkStringArg("label", $label);
		$this->checkStringOrSignalArg("action", $action);
		$this->checkStringOrSignalArg("action_off", $action_off);
		$this->checkBoolArg("is_on", $is_on);

		$this->label = $label;

		if (is_string($action)) {
			$this->action = $action;
		}
		else {
			$this->action = null;
			$this->action_on_signal = new TriggeredSignal($action, "click");
		}

		if (is_string($action_off)) {
			$this->action_off = $action_off;
		}
		else {
			$this->action_off = null;
			$this->action_off_signal = new TriggeredSignal($action_off, "click");
		}

		$this->is_on = $is_on;
	}

	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function isOn()
	{
		return $this->is_on;
	}

	/**
	 * @inheritdoc
	 */
	public function getActionOff()
	{
		if ($this->action_off !== null) {
			return $this->action_off;
		}
		return $this->action_off_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getAction()
	{
		if ($this->action !== null) {
			return $this->action;
		}
		return $this->action_on_signal;
	}

}
