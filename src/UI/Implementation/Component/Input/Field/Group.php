<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Factory as DataFactory;

/**
 * This implements the group input.
 */
class Group extends Input implements C\Input\Field\Group {
	use ComponentHelper;

	/**
	 * Inputs that are contained by this group
	 *
	 * @var    Input[]
	 */
	protected $inputs = [];

	/**
	 * @var	\ilLanguage
	 */
	protected $lng;

	/**
	 * Group constructor.
	 *
	 * @param DataFactory           $data_factory
	 * @param ValidationFactory     $validation_factory
	 * @param \ILIAS\Refinery\Factory $refinery
	 * @param InputInternal[]       $inputs
	 * @param                       $label
	 * @param                       $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		\ILIAS\Refinery\Factory $refinery,
		array $inputs,
		string $label,
		string $byline = null
	) {
		parent::__construct($data_factory, $refinery, $label, $byline);
		$this->checkArgListElements("inputs", $inputs, InputInternal::class);
		$this->inputs = $inputs;
	}

	public function withDisabled($is_disabled) {
		$clone = parent::withDisabled($is_disabled);
		$inputs = [];
		foreach ($this->inputs as $key => $input)
		{
			$inputs[$key] = $input->withDisabled($is_disabled);
		}
		$clone->inputs = $inputs;
		return $clone;
	}

	public function withRequired($is_required) {
		$clone = parent::withRequired($is_required);
		$inputs = [];
		foreach ($this->inputs as $key => $input)
		{
			$inputs[$key] = $input->withRequired($is_required);
		}
		$clone->inputs = $inputs;
		return $clone;
	}

	public function withOnUpdate(Signal $signal) {
		//TODO: use $clone = parent::withOnUpdate($signal); once the exception there
		//is solved.
		$clone = $this->withTriggeredSignal($signal, 'update');
		$inputs = [];
		foreach ($this->inputs as $key => $input) {
			$inputs[$key] = $input->withOnUpdate($signal);
		}
		$clone->inputs = $inputs;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		if (!is_array($value)) {
			return false;
		}
		if (count($this->getInputs()) !== count($value)) {
			return false;
		}
		foreach ($this->getInputs() as $key => $input) {
			if (!array_key_exists($key, $value)) {
				return false;
			}
			if (!$input->isClientSideValueOk($value[$key])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Collects the input, applies trafos and forwards the input to its children and returns
	 * a new input group reflecting the inputs with data that was putted in.
	 *
	 * @inheritdoc
	 */
	public function withInput(InputData $post_input) {
		/**
		 * @var $clone Group
		 */
		$clone = $this;

		if (sizeof($this->getInputs()) === 0) {
			return $clone;
		}

		$inputs = [];
		$contents = [];
		$error = false;

		foreach ($this->getInputs() as $key => $input) {
			$inputs[$key] = $input->withInput($post_input);
			$content = $inputs[$key]->getContent();
			if ($content->isError()) {
				$error = true;
			}
			else {
				$contents[$key] = $content->value();
			}
		}

		$clone->inputs = $inputs;
		if ($error) {
			// TODO: use lng here
			$clone->content = $clone->data_factory->error("error_in_group");
		}
		else {
			$clone->content = $clone->applyOperationsTo($contents);
		}

		if ($clone->content->isError()) {
			$clone = $clone->withError("".$clone->content->error());
		}

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withNameFrom(NameSource $source) {
		$clone = parent::withNameFrom($source);
		/**
		 * @var $clone Group
		 */
		$named_inputs = [];
		foreach ($this->getInputs() as $key => $input) {
			$named_inputs[$key] = $input->withNameFrom($source);
		}

		$clone->inputs = $named_inputs;

		return $clone;
	}

	/**
	 * @return Input[]
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		return null;
	}
}
