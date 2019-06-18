<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;

/**
 * This implements the checkbox input, note that this uses GroupHelper to manage potentially
 * attached dependant groups.
 */
class Checkbox extends Input implements C\Input\Field\Checkbox, C\Changeable, C\Onloadable {

	use JavaScriptBindable;
	use Triggerer;
	use DependantGroupHelper;


	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		return null;
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
	public function withInput(InputData $post_input) {
		if ($this->getName() === null) {
			throw new \LogicException("Can only collect if input has a name.");
		}

		if (!$this->isDisabled()) {
			$value = $post_input->getOr($this->getName(), "");
			$clone = $this->withValue($value);
		}
		else {
			$value = $this->getValue();
			$clone = $this;
		}

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
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').prop('checked').toString());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').prop('checked').toString());";
			return $code;
		};
	}
}
