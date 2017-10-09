<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between standard and primary buttons.
 */
abstract class Button implements C\Button\Button {
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

	/**
	 * @var bool
	 */
	protected $active = true;

	/**
	 * @var string
	 */
	protected $aria_label;

	/**
	 * @var bool
	 */
	protected $aria_checked = false;


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
	public function withLabel($label) {
		$this->checkStringArg("label", $label);
		$clone = clone $this;
		$clone->label = $label;
		return $clone;
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
	public function isActive() {
		return $this->active;
	}

	/**
	 * @inheritdoc
	 */
	public function withUnavailableAction() {
		$clone = clone $this;
		$clone->active = false;
		return $clone;
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
		// Note: The event 'hover' maps to 'mouseenter' in javascript. Although 'hover' is available in JQuery,
		// it encodes the 'mouseenter' and 'mouseleave' events and thus expects two event handlers.
		// In the context of this framework, the signal MUST only be triggered on the 'mouseenter' event.
		// See also: https://api.jquery.com/hover/
		return $this->addTriggeredSignal($signal, 'mouseenter');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnHover(Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'mouseenter');
	}

	/**
	 * @inheritdoc
	 */
	public function withAriaLabel($aria_label)
	{
		$this->checkStringArg("label", $aria_label);
		$clone = clone $this;
		$clone->aria_label = $aria_label;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAriaLabel()
	{
		return $this->aria_label;
	}

	/**
	 * @inheritdoc
	 */
	public function withAriaChecked()
	{
		$clone = clone $this;
		$clone->aria_checked = true;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function isAriaChecked()
	{
		return $this->aria_checked;
	}
}
