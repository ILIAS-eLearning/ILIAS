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
 * @package ILIAS\UI\Component\Dropzone\File
 */

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The standard dropzone is used to drop files dragged from outside
	 *      the browser window.
	 *   composition: Standard dropzones are areas to drop files.
	 *   effect: >
	 *      All dropzones on the page are highlighted
	 *      when the user is dragging files from outside the browser window
	 *      into one dropzone.
	 *   rivals:
	 *      Rival 1: >
	 *          A wrapper dropzone can hold
	 *          other ILIAS UI components instead of a message.
	 *
	 * rules:
	 *   usage:
	 *     1: A page SHOULD contain only one standard dropzone.
	 *     2: Standard dropzones MAY contain a message.
	 *     3: Standard dropzones MAY use the darkened background highlighting.
	 *     4: >
	 *          Standard dropzones with the darkened background highlighting MUST
	 *          NOT be used in modals.
	 *     5: >
	 *          Other ILIAS UI components are REQUIRED to handle dropped files
	 *          further.
	 *   responsiveness:
	 *     1: Standard dropzones SHOULD have a static height.
	 *
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Dropzone\File\Standard
	 */
	public function standard();


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The wrapper dropzone is used to display other ILIAS UI components
	 *      inside the dropzone. The wrapper dropzone is used to drop files
	 *      dragged from outside the browser window.
	 *   composition: >
	 *      The wrapper dropzone uses the darkened background highlighting by
	 *      default and is not visible before the user is dragging files
	 *      from outside the browser window into the browser window.
	 *   effect: >
	 *      All dropzones on the page are highlighted when the user is dragging
	 *      files from outside the browser window into the browser window.
	 *      If a page contains two or more wrapper dropzones, the setting for
	 *      the darkened background of the last rendered dropzone will be
	 *      applied to all wrapper dropzones.
	 *   rivals:
	 *      Rival 1: >
	 *          A standard dropzone can display a message instead of other
	 *          ILIAS UI components.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages SHOULD NOT contain a wrapper dropzone.
	 *     2: Wrapper dropzones MUST contain one or more ILIAS UI components.
	 *     3: Wrapper dropzones MUST NOT contain any other dropzone component.
	 *     4: Wrapper dropzones MAY use the darkened background highlighting.
	 *     5: >
	 *          Wrapper dropzones with the darkened background highlighting MUST
	 *          NOT be used in modals.
	 *     6: >
	 *          Other ILIAS UI components are REQUIRED to handle dropped files
	 *          further.
	 *   style:
	 *     1: >
	 *          The height and the width MUST be determined by the components
	 *          inside.
	 *
	 * ---
	 *
	 * @param Component[]|Component $content an array or a single instance of
	 *                                       ILIAS UI components
	 *
	 * @return \ILIAS\UI\Component\Dropzone\File\Wrapper
	 */
	public function wrapper($content);
}