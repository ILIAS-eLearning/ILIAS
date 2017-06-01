<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button\Split;

use ILIAS\UI\Component as C;

class Standard extends Split implements C\Button\Split\Standard  {

	/**
	 * array of actions, keys are labels
	 */
	protected $actions = array();

	/**
	 * @param array $labelled_actions Set of labelled actions (string|string)[]. The label of the action is used as key, the action itself as value.
	 *        The first of the actions will be used as default action, directly visible.
	 */
	public function __construct($labelled_actions) {
		$this->actions = $labelled_actions;
	}

	/**
	 * @inheritdoc
	 */
	public function getActionsAndLabels() {
		return $this->actions;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefault() {
		if (count($this->actions) > 0) {
			reset($this->actions);
			return key($this->actions);
		}
		return "";
	}

}