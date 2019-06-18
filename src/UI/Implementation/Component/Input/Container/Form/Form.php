<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component as CI;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\Refinery\Transformation;
use ILIAS\Data;
use ILIAS\Refinery;

use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This implements commonalities between all forms.
 */
abstract class Form implements C\Input\Container\Form\Form, CI\Input\NameSource {

	use ComponentHelper;
	use JavaScriptBindable;
	/**
	 * @var    C\Input\Field\Group
	 */
	protected $input_group;
	/**
	 * @var Transformation|null
	 */
	protected $transformation;
	/**
	 * For the implementation of NameSource.
	 *
	 * @var    int
	 */
	private $count = 0;
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @var Signal
	 */
	protected $update_signal;


	/**
	 * @param array $inputs
	 */
	public function __construct(SignalGeneratorInterface $signal_generator, Input\Field\Factory $field_factory, array $inputs) {
		$classes = [CI\Input\Field\Input::class];
		$this->checkArgListElements("input", $inputs, $classes);
		// TODO: this is a dependency and should be treated as such. `use` statements can be removed then.
		$this->input_group = $field_factory->group(
			$inputs,
			"",
			""
		)->withNameFrom($this);
		$this->transformation = null;
		$this->signal_generator = $signal_generator;
		$this->initSignals();
	}


	/**
	 * @inheritdocs
	 */
	public function getInputs() {
		return $this->getInputGroup()->getInputs();
	}


	/**
	 * @inheritdocs
	 */
	public function getInputGroup() {
		return $this->input_group;
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
		$clone->input_group = $this->getInputGroup()->withInput($post_data);

		return $clone;
	}


	/**
	 * @inheritdocs
	 */
	public function withAdditionalTransformation(Transformation $trafo) {
		$clone = clone $this;
		$clone->input_group = $clone->getInputGroup()->withAdditionalTransformation($trafo);

		return $clone;
	}


	/**
	 * @inheritdocs
	 */
	public function getData() {
		$content = $this->getInputGroup()->getContent();
		if (!$content->isok()) {
			return null;
		}

		return $content->value();
	}


	/**
	 * Check the request for sanity.
	 *
	 * TODO: implement me!
	 *
	 * @param    ServerRequestInterface $request
	 *
	 * @return    bool
	 */
	protected function isSanePostRequest(ServerRequestInterface $request) {
		return true;
	}


	/**
	 * Extract post data from request.
	 *
	 * @param    ServerRequestInterface $request
	 *
	 * @return    Input\InputData
	 */
	protected function extractPostData(ServerRequestInterface $request) {
		return new PostDataFromServerRequest($request);
	}


	// Implementation of NameSource

	public function getNewName() {
		$name = "form_input_{$this->count}";
		$this->count++;

		return $name;
	}

	/**
	 * @inheritdoc
	 */
	public function getUpdateSignal() {
		return $this->update_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals() {
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * Set the update signal for this input
	 */
	protected function initSignals() {
		$this->update_signal = $this->signal_generator->create();
	}
}
