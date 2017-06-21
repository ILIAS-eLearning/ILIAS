<?php
namespace ILIAS\UI\Component\Item;
/**
 * This is how a factory for Items looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       This is a standard item to be used in lists or similar contexts.
 	 *   composition: >
	 *       A list item consists of a title and the following optional elements:
	 *       description, action drop down, properties (name/value), a text or
	 *       image lead and a marker.
 	 * rules:
	 *    accessibility:
	 *      1: >
	 *       Do not provide information by a marker ID only, since it will only be presented
	 *       with a colored border. The same information should be presented, e.g. in a property
	 *       to enable screen reader access.
	 * ---
	 * @param string $title Title of the item
	 * @return \ILIAS\UI\Component\Item\StandardItem
	 */
	public function standard($title);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       An Item Group groups items of a certain type.
	 *   composition: >
	 *       An Item Group consists of a header with an optional action Dropdown and
	 *       a list if Items.
	 * ---
	 * @param string $title Title of the group
	 * @param \ILIAS\UI\Component\Item\Item[] $items items
	 * @return \ILIAS\UI\Component\Item\Group
	 */
	public function group($title, $items);
}
