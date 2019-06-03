<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Factory
 *
 * @package ILIAS\UI\Implementation\Component\Input\Field
 */
class Factory implements Field\Factory {

	/**
	 * @var    Data\Factory
	 */
	protected $data_factory;
	/**
	 * @var Validation\Factory
	 */
	protected $validation_factory;
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;
	/**
	 * @var \ILIAS\Refinery\Factory
	 */
	private $refinery;


	/**
	 * Factory constructor.
	 *
	 * @param SignalGeneratorInterface $signal_generator
	 * @param Data\Factory $data_factory
	 * @param \ILIAS\Refinery\Factory $refinery
	 */
	public function __construct(
		SignalGeneratorInterface $signal_generator,
		Data\Factory $data_factory,
		\ILIAS\Refinery\Factory $refinery
	) {
		$this->signal_generator = $signal_generator;
		$this->data_factory = $data_factory;
		$this->refinery = $refinery;
	}

	/**
	 * @inheritdoc
	 */
	public function text($label, $byline = null) {
		return new Text($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function numeric($label, $byline = null) {
		return new Numeric($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function group(array $inputs) {
		return new Group($this->data_factory, $this->refinery, $inputs, "", "");
	}

	/**
	 * @inheritdoc
	 */
	public function optionalGroup(array $inputs, string $label, string $byline = null) : Field\OptionalGroup {
		return new OptionalGroup($this->data_factory, $this->refinery, $inputs, $label, $byline);
	}


	/**
	 * @inheritdoc
	 */
	public function section(array $inputs, $label, $byline = null) {
		return new Section($this->data_factory, $this->refinery, $inputs, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function dependantGroup(array $inputs) {
		return new DependantGroup($this->data_factory, $this->refinery, $this->signal_generator, $inputs);
	}

	/**
	 * @inheritdoc
	 */
	public function checkbox($label, $byline = null) {
		return new Checkbox($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritDoc
	 */
	public function tag(string $label, array $tags, $byline = null): Field\Tag {
		return new Tag($this->data_factory, $this->refinery, $label, $byline, $tags);
	}

	/**
	 * @inheritdoc
	 */
	public function password($label, $byline = null) {
		return new Password($this->data_factory, $this->refinery, $label, $byline, $this->signal_generator);
	}

	/**
	 * @inheritdoc
	 */
	public function select($label, array $options, $byline = null) {
		return new Select($this->data_factory, $this->refinery, $label, $options, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function textarea($label, $byline = null) {
		return new Textarea($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function radio($label, $byline = null) {
		return new Radio($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function multiSelect($label, array $options, $byline = null) {
		return new MultiSelect($this->data_factory, $this->refinery, $label, $options, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function dateTime($label, $byline = null) {
		return new DateTime($this->data_factory, $this->refinery, $label, $byline);
	}

	/**
	 * @inheritdoc
	 */
	public function duration($label, $byline = null) {
		return new Duration($this->data_factory, $this->refinery, $this, $label, $byline);
	}
}
