<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Form;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;

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
		$classes = [C\Input\Input::class];
		$this->checkArgListElements("input", $inputs, $classes);
		$this->inputs = $inputs;
	}

	/**
	 * @inheritdocs
	 */
	public function getInputs() {
		return $this->inputs;
	}
}
