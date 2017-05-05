<?php
/**
 * Interface Droppable
 *
 * Describes a UI component that can handle drop events from the browser.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component
 */

namespace ILIAS\UI\Component;

interface Droppable extends Triggerer {

	/**
	 * Clones this instance and sets the passed in argument on it.
	 *
	 * @param Signal $signal a ILIAS UI signal which is used on drop event
	 *
	 * @return Droppable a copy of this instance
	 */
	function withOnDrop(Signal $signal);


	/**
	 * Clones this instance and appends the passed in argument on it.
	 *
	 * @param Signal $signal a ILIAS UI signal which is used on drop event
	 *
	 * @return Droppable a copy of this instance
	 */
	function appendOnDrop(Signal $signal);

}