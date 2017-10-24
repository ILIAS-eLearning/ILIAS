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
use ILIAS\UI\Implementation\Component\Input\PostData;

/**
 * This implements the checkbox input.
 */
class Checkbox extends Group implements C\Input\Field\Checkbox, C\Changeable {
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var C\Input\Field\SubSection|null
	 */
	protected $sub_section = null;
	/**
	 * Numeric constructor.
	 * @param DataFactory $data_factory
	 * @param $label
	 * @param $byline
	 */
	public function __construct(DataFactory $data_factory, ValidationFactory $validation_factory, TransformationFactory $transformation_factory, $label, $byline) {

		parent::__construct($data_factory, $validation_factory, $transformation_factory, [], $label, $byline);

		//TODO: IsBoolean or similar here
		//$this->setAdditionalConstraint($this->validation_factory->isNumeric());
	}

	/**
	 * Collects the input, applies trafos on the input and returns
	 * a new input reflecting the data that was putted in.
	 *
	 * @inheritdoc
	 */
	public function withInput(PostData $post_input) {
		if ($this->getName() === null) {
			throw new \LogicException("Can only collect if input has a name.");
		}

		$value = $post_input->getOr($this->getName(),"off");
		$clone = $this->withValue($value);
		$clone->content = $this->applyOperationsTo($value);
		if ($clone->content->isError()) {
			return $clone->withError("".$clone->content->error());
		}

		$clone = $clone->withGroupInput($post_input);

		return $clone;
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
	 * @return C\Input\Field\SubSection|null
	 */
	public function getSubSection(){
		if(is_array($this->inputs)){
			return $this->inputs[0];
		}else{
			return null;
		}

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
