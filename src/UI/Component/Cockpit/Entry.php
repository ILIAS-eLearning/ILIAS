<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Cockpit\Entry;

/**
 * Cockpit Bar Entry
 * @package ILIAS\UI\Component\Cockpit
 */
interface Entry extends \ILIAS\UI\Component\Component {

	/**
	 * Get the caption of the entry.
	 *
	 * @return string
	 */
	public function caption();

	/**
	 * Get the icon of the entry.
	 *
	 * @return \Icon
	 */
	public function icon();

	/**
	 * Get the action of the entry.
	 *
	 * @return \Slate|string|null
	 */
	public function action();

	/**
	 * Set the action of the entry.
	 * When set to null, the entry is unclickable.
	 *
	 * @param  \Slate|string|null  $action
	 * @return \Entry
	 */
	public function withAction($action);

	/**
	 * Does this entry trigger a slate?
	 *
	 * @return boolean
	 */
	public function hasSlate();

	/**
	 * Sets the entry's state to active/inactive.
	 *
	 * @param boolean 	$state
	 * @return \Entry
	 */
	public function withActivation($state=true);

}
