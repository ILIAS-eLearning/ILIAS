<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements the checkbox input.
 */
class Checkbox extends Input implements C\Input\Field\Checkbox, C\Changeable {
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * Numeric constructor.
	 * @param DataFactory $data_factory
	 * @param $label
	 * @param $byline
	 */
	public function __construct(DataFactory $data_factory, ValidationFactory $validation_factory, TransformationFactory $transformation_factory, $label, $byline) {

		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);

		//TODO: IsBoolean or similar here
		//$this->setAdditionalConstraint($this->validation_factory->isNumeric());
	}

	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		//TODO: Implement this
		return true;
	}


	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		throw new \LogicException("NYI: What could 'required' mean here?");
	}

	/**
	 * @inheritdoc
	 */
	public function withSubsection(C\Input\Field\SubSection $sub_section){
		$clone = clone $this;
		$clone = $clone->withOnChange($sub_section->getToggleSignal());

		$clone->inputs[0] = $sub_section;
		return $clone;
	}

	/**
	 * @return C\Input\Field\SubSection
	 */
	public function getSubSection(){
		return $this->inputs[0];
	}

	/**
	 * @inheritdoc
	 */
	public function withOnChange(C\Signal $signal) {
		return $this->addTriggeredSignal($signal, 'change');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnChange(C\Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'change');
	}
}
