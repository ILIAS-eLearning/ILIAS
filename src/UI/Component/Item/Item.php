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
	 * @param array<string,string> $properties Label => Content
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

	/**
	 * Set a marker id. Marker IDs must be >=0 and <=32. They assign the item to a CSS class il-item-marker-[ID].
	 * Set 0 for no marker (default).
	 *
	 * @param int $marker_id
	 */
	public function withMarkerId($marker_id);

	/**
	 * @return int
	 */
	public function getMarkerId();

	/**
	 * Set image as lead
	 *
	 * @param \ILIAS\UI\Component\Image\Image $image lead image
	 */
	public function withLeadImage(\ILIAS\UI\Component\Image\Image $image);

	/**
	 * Set image as lead
	 *
	 * @param string $text lead text
	 */
	public function withLeadText($text);

	/**
	 * Reset lead to null
	 */
	public function withNoLead();

	/**
	 * @return null|string|\ILIAS\UI\Component\Image\Image
	 */
	public function getLead();

}
