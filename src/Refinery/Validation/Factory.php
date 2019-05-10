<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Validation;
use ILIAS\Data;

/**
 * Factory for creating constraints.
 */
class Factory {
	const LANGUAGE_MODULE = "validation";

	/**
	 * @var Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	/**
	 * Factory constructor.
	 *
	 * @param Data\Factory $data_factory
	 */
	public function __construct(Data\Factory $data_factory, \ilLanguage $lng) {
		$this->data_factory = $data_factory;
		$this->lng = $lng;
		$this->lng->loadLanguageModule(self::LANGUAGE_MODULE);
	}

	// SOME RESTRICTIONS

	/**
	 * Get a constraint for a array with constraint to all elements.
	 *
	 * @param Constraint $on_element
	 *
	 * @return Constraints\IsArrayOf
	 */
	public function isArrayOf(Constraint $on_element) {
		return new Constraints\IsArrayOf($this->data_factory, $on_element, $this->lng);
	}

	/**
	 * Get the constraint that some value is a number
	 *
	 * @return  Constraint
	 */
	public function isNumeric() {
		return new Constraints\IsNumeric($this->data_factory, $this->lng);
	}

	/**
	 * Get the constraint that some value is null
	 *
	 * @return  Constraint
	 */
	public function isNull() {
		return new Constraints\IsNull($this->data_factory, $this->lng);
	}

	/**
	 * Get the factory for password constraints.
	 *
	 * @return   ILIAS\Refinery\Validation\Constraints\Password\Factory;
	 */
	public function password() {
		return new Constraints\Password\Factory($this->data_factory, $this->lng);
	}
}
