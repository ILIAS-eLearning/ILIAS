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
 * This implements the checkbox input, note that this extends group to manage potential
 * attached dependant groups.
 */
class Checkbox extends Group implements C\Input\Field\Checkbox, C\Changeable, C\Onloadable {

	use JavaScriptBindable;
	use Triggerer;
	/**
	 * @var C\Input\Field\DependantGroup|null
	 */
	protected $dependant_group = null;


	/**
	 * Checkbox constructor.
	 *
	 * @param DataFactory           $data_factory
	 * @param ValidationFactory     $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param                       $label
	 * @param                       $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, [], $label, $byline);
	}


	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		if ($value == "checked" || $value === "") {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @inheritdoc
	 * @return Checkbox
	 */
	public function withValue($value) {
		//be lenient to bool params for easier use
		if ($value === true) {
			$value = "checked";
		} else {
			if ($value === false) {
				$value = "";
			}
		}

		return parent::withValue($value);
	}


	/**
	 * @inheritdoc
	 */
	public function withInput(PostData $post_input) {
		if ($this->getName() === null) {
			throw new \LogicException("Can only collect if input has a name.");
		}

		$value = $post_input->getOr($this->getName(), "");
		$clone = $this->withValue($value);
		$clone->content = $this->applyOperationsTo($value);
		if ($clone->content->isError()) {
			return $clone->withError("" . $clone->content->error());
		}

		$clone = $clone->withGroupInput($post_input);

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function withDependantGroup(C\Input\Field\DependantGroup $dependant_group) {
		$clone = clone $this;
		/**
		 * @var $clone           Checkbox
		 * @var $dependant_group DependantGroup
		 */
		$clone = $clone->withOnChange($dependant_group->getToggleSignal());
		$clone = $clone->appendOnLoad($dependant_group->getInitSignal());

		$clone->inputs["dependant_group"] = $dependant_group;

		return $clone;
	}


	/**
	 * @return C\Input\Field\DependantGroup|null
	 */
	public function getDependantGroup() {
		if (is_array($this->inputs)) {
			return $this->inputs["dependant_group"];
		} else {
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


	/**
	 * @inheritdoc
	 */
	public function withOnLoad(C\Signal $signal) {
		return $this->addTriggeredSignal($signal, 'load');
	}


	/**
	 * @inheritdoc
	 */
	public function appendOnLoad(C\Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'load');
	}
}
