<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;


/**
 * This implements commonalities between groups of inputs (e.g. form sections).
 */
class Group extends Input implements C\Input\Field\Group{

	/**
	 * Inputs that are contained by this group
	 *
	 * @var	Input[]
	 */
	protected $inputs;

	/**
	 * Group constructor.
	 * @param DataFactory $data_factory
	 * @param ValidationFactory $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param $inputs
	 * @param $label
	 * @param $byline
	 */
	public function __construct(DataFactory $data_factory,ValidationFactory $validation_factory,
	                            TransformationFactory $transformation_factory,
	                            $inputs, $label, $byline) {
		parent::__construct($data_factory, $validation_factory,$transformation_factory, $label, $byline);
		$this->inputs = $inputs;
	}

	/**
	 * Get the value that is displayed in the groups input as Generator instance.
	 *
	 * @return array
	 */
	public function getValue() {
		$values = [];
		foreach($this->getInputs() as $key => $input){
			$values[$key] = $input->getValue();
		}
		return $values;
	}

	/**
	 * Get an input like this with children with other values displayed on the
	 * client side. Note that the number of values passed must match the number of inputs.
	 *
	 * @param	array
	 * @throws  \InvalidArgumentException    if value does not fit client side input
	 * @return Input
	 */
	public function withValue($values) {
		$this->checkArg("value", $this->isClientSideValueOk($values),
			"Display value does not match input type.");
		$clone = clone $this;
		$inputs = [];

		foreach($this->getInputs() as $key => $input){
			$inputs[$key] = $input->withValue($values[$key]);
		}

		$clone->inputs = $inputs;
		return $clone;
	}

	/**
	 * Default implementation for groups. May be overriden if more specific checks are needed.
	 *
	 * @param	mixed	$value
	 * @return	bool
	 */
	protected function isClientSideValueOk($value){
		if(!is_array($value)){
			return false;


		}
		if(!sizeof($this->getInputs() == sizeof($value))){
			return false;
		}

		foreach($this->getInputs() as $key => $input){
			if(!array_key_exists($key,$value)){
				return false;
			}
		}
		return true;
	}

	/**
	 * Default implementation for this is returning null for groups. Todo: might be improved
	 *
	 * @return	null
	 */
	protected function getConstraintForRequirement(){
		return null;
	}

	/**
	 * Collects the input, applies trafos and forwards the input to its children and returns
	 * a new input group reflecting the inputs with data that was putted in.
	 *
	 * @inheritdoc
	 */
	public function withInput(PostData $post_input) {
		$clone = clone $this;
		$inputs = [];
		$values = [];
		foreach($this->getInputs() as $key => $input){
			$inputs[$key] = $input->withInput($post_input);
			//Todo: Is this correct here or should it be getValue? Design decision...
			$content = $inputs[$key]->getContent();
			if( $content->isOk()){
				$values[$key] = $content->value();
			}
		}
		$clone->inputs = $inputs;
		$clone->content = $clone->applyOperationsTo($values);

		if ($clone->content->isError()) {
			return $clone->withError("".$clone->content->error());
		}
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withNameFrom(NameSource $source) {
		$clone = clone $this;

		$named_inputs = [];
		foreach($this->getInputs() as $key => $input) {
			$named_inputs[$key] = $input->withNameFrom($source);
		}

		$clone->inputs = $named_inputs;
		return $clone;
	}

	/**
	 * @return Input[]
	 */
	public function getInputs()
	{
		return $this->inputs;
	}
}
