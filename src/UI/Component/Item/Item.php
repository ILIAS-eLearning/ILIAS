<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Common interface to all items.
 */
interface Item extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the item 
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Create a new item with an attached description.
	 * @param string $description
	 * @return AppointmentItem
	 */
	public function withDescription($description);

	/**
	 * Get the description of the item.
	 * @return string
	 */
	public function getDescription();

	/**
	 * Get a new item with the given properties as key-value pairs.
	 *
	 * The key is holding the title and the value is holding the content of the
     * specific data set.
	 *
	 * @param array<string,string> $properties Title => Content
	 * @return self
	 */
	public function withProperties(array $properties);

	/**
	 * Get the properties of the appointment.
	 *
	 * @return array<string,string>		Title => Content
	 */
	public function getProperties();

	/**
	 * Create a new appointment item with a set of actions to perform on it.
	 * Those actions will be listed in the dropdown on the right side of the
	 * appointment.
	 *
	 * @param string[] $actions
	 * @return AppointmentItem
	 */
	public function withActions(array $actions);

	/**
	 * Get the actions of the item.
	 *
	 * @return string[]
	 */
	public function getActions();
}
