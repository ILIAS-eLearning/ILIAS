<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Toggle extends Button implements C\Button\Toggle {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string|null
	 */
	protected $action_activated;

	/**
	 * @var string|null
	 */
	protected $action_deactivated;

	public function __construct($action_activated, $action_deactivated)
	{
		$this->checkArg(
			"action_activated",
			is_string($action_activated) || $action_activated instanceof Signal,
			$this->wrongTypeMessage("string or Signal", gettype($action_activated))
		);
		if (is_string($action_activated)) {
			$this->action_activated = $action_activated;
		}
		else {
			$this->action_activated = null;
			$this->setTriggeredSignal($action_activated, "click");
		}

		$this->checkArg(
			"action_deactivated",
			is_string($action_deactivated) || $action_deactivated instanceof Signal,
			$this->wrongTypeMessage("string or Signal", gettype($action_deactivated))
		);
		if (is_string($action_deactivated)) {
			$this->action_deactivated = $action_deactivated;
		}
		else {
			$this->action_deactivated = null;
			$this->setTriggeredSignal($action_deactivated, "click");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getActionActivated()
	{
		if ($this->action_activated !== null) {
			return $this->action_activated;
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

	/**
	 * @inheritdoc
	 */
	public function getActionDeactivated()
	{
		if ($this->action_deactivated !== null) {
			return $this->action_deactivated;
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
