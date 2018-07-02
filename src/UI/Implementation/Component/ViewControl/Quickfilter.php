<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Quickfilter implements Component\ViewControl\Quickfilter  {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var Signal
	 */
	protected $select_signal;

	/**
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var string
	 */
	protected $target_url;

	/**
	 * @var string
	 */
	protected $paramter_name="quickfilter";

	/**
	 * @var string
	 */
	protected $active;

	/**
	 * @var arrary<string,string>
	 */
	protected $options=array();

	/**
	 * @var string | int
	 */
	protected $default_value;


	public function __construct(array $options, SignalGeneratorInterface $signal_generator) {
		$this->options = $options;
		$this->signal_generator = $signal_generator;
		$this->initSignals();
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals() {
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * Set the signals for this component
	 */
	protected function initSignals() {
		$this->select_signal = $this->signal_generator->create();
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
	public function withTargetURL($url, $paramter_name) {
		$this->checkStringArg("url", $url);
		$this->checkStringArg("paramter_name", $paramter_name);
		$clone = clone $this;
		$clone->target_url = $url;
		$clone->paramter_name = $paramter_name;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetURL() {
		return $this->target_url;
	}

	/**
	 * @inheritdoc
	 */
	public function getParameterName() {
		return $this->paramter_name;
	}

	/**
	 * @inheritdoc
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @inheritdoc
	 */
	public function withOnSort(Component\Signal $signal) {
		return $this->addTriggeredSignal($signal, 'sort');
	}

	/**
	 * @inheritdoc
	 */
	public function getSelectSignal() {
		return $this->select_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function withDefaultValue($default_value) {
		assert('is_string($default_value) || is_numeric($default_value)');
		$clone = clone $this;
		$clone->default_value = $default_value;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultValue() {
		return $this->default_value;
	}
}
