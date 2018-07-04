<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Toggle extends Button implements C\Button\Toggle {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var bool
	 */
	protected $is_on;

	/**
	 * @var string|null
	 */
	protected $action_off;

	public function __construct($label, $action, $action_off, $is_on)
	{
		parent::__construct($label, $action);

		$this->checkStringOrSignalArg("action_off", $action_off);
		if (is_string($action_off)) {
			$this->action_off = $action_off;
		}
		else {
			$this->action_off = null;
			$this->setTriggeredSignal($action_off, "click");
		}
		$this->checkBoolArg("is_on", $is_on);
		$this->is_on = $is_on;
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
		$triggered_click_signals = $this->triggered_signals["click"];
		if ($triggered_click_signals === null) {
			return [];
		}
		return array_map(
			function($ts) { return $ts->getSignal(); },
			$triggered_click_signals
		);
	}

}
