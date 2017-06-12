<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between standard and primary buttons.
 */
abstract class Link implements C\Link\Link {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $action;

	public function __construct($action) {
		$this->checkStringArg("action", $action);
		$this->action = $action;
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
