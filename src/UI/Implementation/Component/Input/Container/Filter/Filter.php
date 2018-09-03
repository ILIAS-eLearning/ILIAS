<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component as CI;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This implements commonalities between all Filters.
 */
abstract class Filter implements C\Input\Container\Filter\Filter, CI\Input\NameSource {

	use ComponentHelper;

	/**
	 * @var    C\Input\Field\Group
	 */
	protected $input_group;

	/**
	 * For the implementation of NameSource.
	 *
	 * @var    int
	 */
	private $count = 0;


	/**
	 * @param array $inputs
	 */
	public function __construct(array $inputs) {
		$classes = [CI\Input\Field\Input::class];
		$this->checkArgListElements("input", $inputs, $classes);
		$input_factory = (new I\Factory())->input();
		$this->input_group = $input_factory->field()->group($inputs)->withNameFrom($this);
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


	// Implementation of NameSource

	public function getNewName() {
		$name = "filter_input_{$this->count}";
		$this->count++;

		return $name;
	}
}
