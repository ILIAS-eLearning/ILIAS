<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;

/**
 * This is a trait for inputs providing dependant groups, such as checkboxes, e.g.
 */
trait DependantGroupHelper {

	/**
	 * @var C\Input\Field\DependantGroup|null
	 */
	protected $dependant_group = null;

	use GroupHelper;

	/**
	 * @inheritdoc
	 */
	public function withDependantGroup(C\Input\Field\DependantGroup $dependant_group) :C\Input\Field\Input {
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
		if (is_array($this->inputs) && array_key_exists("dependant_group",$this->inputs)) {
			return $this->inputs["dependant_group"];
		} else {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnLoad(C\Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'load');
	}

	/**
	 * @inheritdoc
	 */
	public function withOnChange(C\Signal $signal) {
		return $this->withTriggeredSignal($signal, 'change');
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
		return $this->withTriggeredSignal($signal, 'load');
	}

}
