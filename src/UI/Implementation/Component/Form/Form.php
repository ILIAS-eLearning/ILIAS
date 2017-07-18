<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Form;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;

/**
 * This implements commonalities between all forms.
 */
abstract class Form implements C\Form\Form {
	use ComponentHelper;

	/**
	 * @var	C\Input\Input[]
     */
	protected $inputs;

	public function __construct(array $inputs) {
		$classes = [CI\Input\Input::class];
		$this->checkArgListElements("input", $inputs, $classes);
		$this->inputs = $inputs;
	}

	/**
	 * @inheritdocs
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * Get the inputs with a consecutive name.
	 *
	 * @return	\ILIAS\UI\Component\Input\Input[]
	 */
	public function getNamedInputs() {
		$counter = 0;
		$named_inputs = [];
		foreach($this->getInputs() as $input) {
			$named_inputs[] = $input->withName("name_$counter");
			$counter++;
		}
		return $named_inputs;
	}
}
