<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Transformation\Transformation;
use ILIAS\Validation\Constraint;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements C\Input\Input {
	use ComponentHelper;

	/**
	 * @var DataFactory
	 */
	protected $data_factory;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $byline;

	/**
	 * This is the value contained in the input as displayed
	 * client side.
	 *
	 * @var	mixed
	 */
	protected $client_side_value;

	/**
	 * @var	string|null
	 */
	protected $name;

	/**
	 * This is an error on the input as displayed client side.
	 *
	 * @var	string|null
	 */
	protected $client_side_error;

	/**
	 * This is the current content of the input in the abstraction.
	 *
	 * @var	Result|null
	 */
	protected $content;

	/**
	 * @var (Transformation|Constraint)[]
	 */
	protected $operations;

	public function __construct(DataFactory $data_factory, $label, $byline) {
		$this->data_factory = $data_factory;
		$this->checkStringArg("label", $label);
		$this->checkStringArg("byline", $byline);
		$this->label = $label;
		$this->byline= $byline;
		$this->client_side_value = null;
		$this->name = null;
		$this->client_side_error = null;
		$this->content = null;
		$this->operations = [];
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
	public function withLabel($label) {
		$this->checkStringArg("label", $label);
		$clone = clone $this;
		$clone->label = $label;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getByline() {
		return $this->byline;
	}

	/**
	 * @inheritdoc
	 */
	public function withByline($byline) {
		$this->checkStringArg("byline", $byline);
		$clone = clone $this;
		$clone->byline = $byline;
		return $clone;
	}

	/**
	 * Get the value that is displayed in the input client side.
	 *
	 * @return	mixed
	 */
	public function getClientSideValue() {
		return $this->client_side_value;
	}

	/**
	 * Get an input like this with another value displayed on the
	 * client side.
	 *
	 * @param	mixed
	 * @throws  \InvalidArgumentException    if value does not fit client side input
	 * @return Input
	 */
	public function withClientSideValue($value) {
		$this->checkArg("value", $this->isClientSideValueOk($value),
			"Display value does not match input type.");
		$clone = clone $this;
		$clone->client_side_value = $value;
		return $clone;
	}

	/**
	 * Check if the value is good to be displayed client side.
	 *
	 * @param	mixed	$value
	 * @return	bool
	 */
	abstract protected function isClientSideValueOk($value);

	/**
	 * The name of the input as used in HTML.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get an input like this one, with a different name.
	 *
	 * @param	string
	 * @return	Input
	 */
	public function withName($name) {
		$this->checkStringArg("name", $name);
		$clone = clone $this;
		$clone->name = $name;
		return $clone;
	}

	/**
	 * The error of the input as used in HTML.
	 *
	 * @return string
	 */
	public function getClientSideError() {
		return $this->client_side_error;
	}

	/**
	 * Get an input like this one, with a different error.
	 *
	 * @param	string
	 * @return	Input
	 */
	public function withClientSideError($error) {
		$this->checkStringArg("error", $error);
		$clone = clone $this;
		$clone->client_side_error = $error;
		return $clone;
	}

	/**
	 * Get an input like this with input from an array.
	 *
	 * Collects the input, applies trafos on the input and returns
	 * a new input reflecting the data that was putted in.
	 *
	 * TODO: We want to use the HTTP-interface here.
	 *
	 * @param	array<string,mixed>		$input
	 * @return	Input
	 */
	public function withInput(array $input) {
		if ($this->name === null) {
			throw new \LogicException("Can only collect if input has a name.");
		}

		$value = $this->valueFromArray($input);
		$clone = $this->withClientSideValue($value);
		$clone->content = $this->applyOperationsTo($value);
		if ($clone->content->isError()) {
			return $clone->withClientSideError("".$clone->content->error());
		}
		return $clone;
	}

	/**
	 * Applies the operations in this instance to the value.
	 *
	 * @param	mixed	$res
	 * @return	Result
	 */
	protected function applyOperationsTo($res) {
		$res = $this->data_factory->ok($res);
		foreach ($this->operations as $op) {
			if ($res->isError()) {
				return $res;
			}

			// TODO: I could make this go away by giving Transformation and
			// Constraint a common interface for that.
			if ($op instanceof Transformation) {
				$res = $res->map($op);
			}
			elseif ($op instanceof Constraint) {
				$res = $op->restrict($res);
			}
		}
		return $res;
	}

	/**
	 * Get the value for this input from an array as POST would contain it.
	 *
	 * @param	array<string,mixed>		$input
	 * @return 	mixed
	 */
	protected function valueFromArray(array $input) {
		$name = $this->getName();
		if (isset($input[$name])) {
			return $input[$name];
		}
		return null;
	}

	/**
	 * Get the current content of the input.
	 *
	 * @return	Result|null
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Apply a transformation to the current or future content.
	 *
	 * @param	Transformation $trafo
	 * @return	Input
	 */
	public function withTransformation(Transformation $trafo) {
		$clone = clone $this;
		$clone->operations[] = $trafo;
		if ($clone->content !== null) {
			$clone->content = $clone->content->map($trafo);
		}		
		return $clone;	
	}
}
