<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Signal;
use ILIAS\Refinery\Transformation\Factory as TransformationFactory;
use ILIAS\Refinery\Validation\Factory as ValidationFactory;

/**
 * This implements the text input.
 */
class Text extends Input implements C\Input\Field\Text {
	/**
	 * @inheritdoc
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->setAdditionalTransformation($transformation_factory->custom(function($v) {
			return strip_tags($v);
		}));
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

	/**
	 * @inheritdoc
	 */
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
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
