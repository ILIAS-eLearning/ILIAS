<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component as CI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This implements commonalities between all Filters.
 */
abstract class Filter implements C\Input\Container\Filter\Filter, CI\Input\NameSource {

	use ComponentHelper;

	/**
	 * @var string|Signal
	 */
	protected $toggle_action_on;

	/**
	 * @var string|Signal
	 */
	protected $toggle_action_off;

	/**
	 * @var string|Signal
	 */
	protected $expand_action;

	/**
	 * @var string|Signal
	 */
	protected $collapse_action;

	/**
	 * @var string|Signal
	 */
	protected $apply_action;

	/**
	 * @var string|Signal
	 */
	protected $reset_action;

	/**
	 * @var    C\Input\Field\Group
	 */
	protected $input_group;

	/**
	 * @var array
	 */
	protected $is_input_rendered;

	/**
	 * @var bool
	 */
	protected $is_activated;

	/**
	 * @var bool
	 */
	protected $is_expanded;

	/**
	 * For the implementation of NameSource.
	 *
	 * @var    int
	 */
	private $count = 0;


	/**
	 * @param string|Signal $toggle_action_on
	 * @param string|Signal $toggle_action_off
	 * @param string|Signal $expand_action
	 * @param string|Signal $collapse_action
	 * @param string|Signal $apply_action
	 * @param string|Signal $reset_action
	 * @param array $inputs
	 * @param array $is_input_rendered
	 * @param bool $is_activated
	 * @param bool $is_expanded
	 */
	public function __construct($toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action,
								array $inputs, array $is_input_rendered, $is_activated, $is_expanded) {
		$this->toggle_action_on = $toggle_action_on;
		$this->toggle_action_off = $toggle_action_off;
		$this->expand_action = $expand_action;
		$this->collapse_action = $collapse_action;
		$this->apply_action = $apply_action;
		$this->reset_action = $reset_action;
		//No further handling for actions needed here, will be done in constructors of the respective component

		if (count($inputs) != count($is_input_rendered)) {
			throw new \ArgumentCountError("Inputs and boolean values must be arrays of same size.");
		} else {
			$classes = [CI\Input\Field\Input::class];
			$this->checkArgListElements("input", $inputs, $classes);
			$input_factory = (new I\Factory())->input();
			$this->input_group = $input_factory->field()->group($inputs)->withNameFrom($this);

			foreach ($is_input_rendered as $r) {
				$this->checkBoolArg("is_input_rendered", $r);
			}
			$this->is_input_rendered = $is_input_rendered;
		}

		$this->checkBoolArg("is_activated", $is_activated);
		$this->is_activated = $is_activated;
		$this->checkBoolArg("is_expanded", $is_expanded);
		$this->is_expanded = $is_expanded;
	}


	/**
	 * @inheritdoc
	 */
	public function getToggleOnAction()
	{
		return $this->toggle_action_on;
	}

	/**
	 * @inheritdoc
	 */
	public function getToggleOffAction()
	{
		return $this->toggle_action_off;
	}

	/**
	 * @inheritdoc
	 */
	public function getExpandAction()
	{
		return $this->expand_action;
	}

	/**
	 * @inheritdoc
	 */
	public function getCollapseAction()
	{
		return $this->collapse_action;
	}


	/**
	 * @inheritdoc
	 */
	public function getApplyAction()
	{
		return $this->apply_action;
	}

	/**
	 * @inheritdoc
	 */
	public function getResetAction()
	{
		return $this->reset_action;
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
	 * @return    PostData
	 */
	protected function extractPostData(ServerRequestInterface $request) {
		return new PostDataFromServerRequest($request);
	}


	// Implementation of NameSource

	public function getNewName() {
		$name = "filter_input_{$this->count}";
		$this->count++;

		return $name;
	}

	/**
	 * @inheritdoc
	 */
	public function isActivated()
	{
		return $this->is_activated;
	}

	/**
	 * @inheritdoc
	 */
	public function withActivated()
	{
		$clone = clone $this;
		$clone->is_activated = true;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withDeactivated()
	{
		$clone = clone $this;
		$clone->is_activated = false;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function isExpanded()
	{
		return $this->is_expanded;
	}

	/**
	 * @inheritdoc
	 */
	public function withExpanded()
	{
		$clone = clone $this;
		$clone->is_expanded = true;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withCollapsed()
	{
		$clone = clone $this;
		$clone->is_expanded = false;
		return $clone;
	}
}
