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
	use GroupHelper;

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
		$clone = parent::withOnUpdate($signal);
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
	public function getUpdateOnLoadCode(): \Closure
	{
		return function () {
			/*
			 * Currently, there is no use case for Group here. The single Inputs
			 * within the Group are responsible for handling getUpdateOnLoadCode().
			 */
		};
	}
}
