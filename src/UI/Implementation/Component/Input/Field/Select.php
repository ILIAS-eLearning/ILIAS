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
		$label,
		$options,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->options = $options;
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
		return 
			in_array($value, array_keys($this->options))
			|| (!$this->isRequired() && $value == "");
	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		return $this->validation_factory->hasMinLength(1);
	}

	/**
	 * @inheritdoc
	 */
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id option:selected').text());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id option:selected').text());";
			return $code;
		};
	}

	/**
	 * @inheritdoc
	 */
	public function withOnUpdate(Signal $signal)
	{
		// TODO: This method will need to be removed.
		// See ILIAS\UI\Implementation\Component\Input\Field\Input
		return $this->withTriggeredSignal($signal, 'update');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnUpdate(Signal $signal)
	{
		// TODO: This method will need to be removed.
		// See ILIAS\UI\Implementation\Component\Input\Field\Input
		return $this->appendTriggeredSignal($signal, 'update');
	}
}
