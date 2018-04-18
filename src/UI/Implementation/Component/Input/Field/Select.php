<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;

/**
 * This implements the select.
 */
class Select extends Input implements C\Input\Field\Select {

	protected $options;
	protected $label;
	protected $disabled_string;
	protected $none_option;
	protected $value;

	/**
	 * Select constructor.
	 *
	 * @param DataFactory           $data_factory
	 * @param ValidationFactory     $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param array                 $options
	 * @param string                $label
	 * @param string                $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		$options,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->options = $options;
	}

	/**
	 * set the disabled_string as true.
	 * @return Select
	 */
	public function withFirstOptionDisabled()
	{
		$clone = clone($this);
		$clone->disabled_string = true;
		return $clone;
	}

	/**
	 * check if the the select has the first option disabled.
	 * @return mixed
	 */
	public function hasFirstOptionDisabled()
	{
		return $this->disabled_string;
	}

	/**
	 * @return array with the key/value options.
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		return is_string($value);
	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		return $this->validation_factory->hasMinLength(1);
	}

	/*
	public function withNoneOption()
	{
		$clone = clone($this);
		$clone->none_option = true;
		return $clone;
	}

	public function hasNoneOption()
	{
		return $this->none_option;
	}
	*/

}