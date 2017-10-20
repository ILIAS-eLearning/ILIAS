<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * This implements commonalities between inputs.
 */
class SubSection extends Group implements C\Input\Field\SubSection, Triggerable {
	use JavaScriptBindable;

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @var Signal
	 */
	protected $toggle_signal;

	/**
	 * SubSection constructor.
	 * @param DataFactory $data_factory
	 * @param ValidationFactory $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param SignalGeneratorInterface $signal_generator
	 * @param $inputs
	 * @param $label
	 * @param $byline
	 */
	public function __construct(DataFactory $data_factory,ValidationFactory $validation_factory,
	                            TransformationFactory $transformation_factory,
	                            SignalGeneratorInterface $signal_generator,
	                            $inputs) {
		parent::__construct($data_factory, $validation_factory,$transformation_factory,$inputs, "", "");
		$this->inputs = $inputs;
		$this->signal_generator = $signal_generator;
		$this->initSignals();
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals() {
		$clone = clone $this;
		$this->initSignals();
		return $clone;
	}

	/**
	 * Set the toggle signal for the sub section
	 */
	protected function initSignals() {
		$this->toggle_signal = $this->signal_generator->create();
	}

	/**
	 * @return Signal
	 */
	public function getToggleSignal() {
		return $this->toggle_signal;
	}

}
