<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Form;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This implements commonalities between all forms.
 */
abstract class Form implements C\Form\Form, CI\Input\NameSource {
	use ComponentHelper;

	/**
	 * @var	C\Input\Input[]
     */
	protected $inputs;

	/**
	 * For the implementation of NameSource.
	 *
	 * @var	int
	 */
	private $count = 0;

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

	// Internal to be used in the form processing machinery.

	/**
	 * Get the inputs with a consecutive name.
	 *
	 * @return	\ILIAS\UI\Component\Input\Input[]
	 */
	public function getNamedInputs() {
		$counter = 0;
		$named_inputs = [];
		foreach($this->getInputs() as $input) {
			$named_inputs[] = $input->withNameFrom($this);
			$counter++;
		}
		// TODO: This might be cached, as it will mostly be used
		// twice on every request, once for rendering, once for
		// input retrieval.
		return $named_inputs;
	}

	/**
	 * Get actual input from an HTTP-Request
	 *
	 * Returns an array containing the inputs according to
	 * the contained inputs.
	 *
	 * @param	ServerRequestInterface	$request
	 * @return	mixed[]
	 */
	public function getPostInput(ServerRequestInterface $request) {
	}

	// Implementation of NameSource

	public function getNewName() {
		$name = "name_{$this->count}";
		$this->count++;
		return $name;
	}
}
