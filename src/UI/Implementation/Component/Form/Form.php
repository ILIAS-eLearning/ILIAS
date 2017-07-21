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
		$this->inputs = $this->nameInputs($inputs);
	}

	/**
	 * @inheritdocs
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * @inheritdocs
	 */
	public function withRequest(ServerRequestInterface $request) {
		if (!$this->isSanePostRequest($request)) {
			throw new \LogicException("Server request is not a valid post request.");
		}
		$post_data = $this->extractPostData($request);

		$clone = clone $this;
		$clone->inputs = [];
		foreach ($this->getInputs() as $input) {
			$clone->inputs[] = $input->withInput($post_data);
		}
		
		return $clone;
	}

	/**
	 * Assign names to the inputs.
	 *
	 * @param	CI\Input\Input
	 * @return	CI\Input\Input
	 */
	protected function nameInputs(array $inputs) {
		$named_inputs = [];
		foreach($inputs as $input) {
			$named_inputs[] = $input->withNameFrom($this);
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
	protected function getPostInput(ServerRequestInterface $request) {
		if (!$this->isSanePostRequest($request)) {
			throw new \LogicException("Server request is not a valid post request.");
		}
		$post_data = $this->extractPostData($request);
		$inputs = $this->getInputs();
		$result = [];
		foreach ($inputs as $input) {
			$result[] = $input
				->withInput($post_data)
				->getContent();
		}
		return $result;
	}

	/**
	 * Check the request for sanity.
	 *
	 * TODO: implement me!
	 *
	 * @param	ServerRequestInterface	$request
	 * @return	bool
	 */
	protected function isSanePostRequest(ServerRequestInterface $request) {
		return true;
	}

	/**
	 * Extract post data from request.
	 *
	 * @param	ServerRequestInterface	$request
	 * @return	PostData
	 */
	protected function extractPostData(ServerRequestInterface $request) {
		return new PostDataFromServerRequest($request);
	}

	// Implementation of NameSource

	public function getNewName() {
		$name = "name_{$this->count}";
		$this->count++;
		return $name;
	}
}
