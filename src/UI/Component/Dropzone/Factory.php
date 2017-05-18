<?php
/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI Dropzone components.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

use ILIAS\UI\Component\Component;

interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The standard dropzone is used to provide a simple dropzone area.
	 *      A massage can be displayed inside the dropzone.
	 *   composition: >
	 *      The standard dropzone highlights with a bright yellow on drag enter.
	 *   effect: >
	 *      If the darkened background is set to true, every Dropzone on the page will be highlighted.
	 *      On file drop event, this dropzones triggers all registered signals with the event data.
	 *   rivals:
	 *     Rival 1: A wrapper dropzone can hold other ILIAS UI components instead of a message.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages should not have a standard dropzone.
	 *     2: A page with a standard dropzone should not contain more than one of them.
	 *   interaction:
	 *     1: A user drops a file into the dropzone area to trigger a signal.
	 *     2: Any file dropped from a user will not be uploaded through this dropzone.
	 *     3: The standard dropzone only listens on file drop events by a user.
	 *   responsiveness:
	 *     1: The standard dropzone has a static height.
	 *
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Dropzone\Standard
	 */
	public function standard();


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The wrapper dropzone is used to display other ILIAS UI components
	 *      inside the dropzone.
	 *   composition: >
	 *      The wrapper dropzone uses the darkened background by default and is not visible before the drag enter event.
	 *   effect: >
	 *      Every Dropzone on the page will be highlighted on dragenter on the html document by the user.
	 *      If a page contains two or more wrapper dropzones, the setting for the darkened background
	 *      of the last rendered dropzone will be used.
	 *   rivals:
	 *     Rival 1: A standard dropzone can display a message instead of other ILIAS UI components.
	 *
	 * context: >
	 *     - provide a dropzone on a calendar event
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages should not use the wrapper dropzone.
	 *   interaction:
	 *     1: A user drops a file into the dropzone area to trigger a signal.
	 *     2: Any file dropped from a user will not be uploaded through this dropzone.
	 *     3: The wrapper dropzone only listens on file drop events by a user.
	 *   style:
	 *     1: This dropzone does not have any margin.
	 *     2: This dropzone does have a 1px padding to display a possible margin on the inner elements.
	 *     3: The height and the width is determined by the components inside.
	 *
	 * ---
	 *
	 * @param Component[]|Component $content an array or a single instance of ILIAS UI components
	 *
	 * @return \ILIAS\UI\Component\Dropzone\Wrapper
	 */
	public function wrapper($content);

}