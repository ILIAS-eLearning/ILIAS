<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI Dropzone components.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The standard dropzone is used to drop files dragged from outside
	 *      the browser window. The dropped files are presented to the user and
	 *      can be uploaded to the server.
	 *   composition: >
	 *      Standard dropzones consist of a visible area where files can
	 *      be dropped. They contain a message explaining that it is possible to
	 *      drop files inside. The dropped files are presented to the user along
	 *      with some button to start the upload process.
	 *   effect: >
	 *      A Standard dropzone is highlighted when the user is dragging files
	 *      over the dropzone.
	 *   rivals:
	 *      Rival 1: >
	 *          A wrapper dropzone can hold other ILIAS UI components instead of a message.
	 *
	 * rules:
	 *   usage:
	 *     1: A page SHOULD contain only one standard dropzone.
	 *     2: Standard dropzones MAY contain a message.
	 *     3: >
	 *        Standard dropzones MUST offer the possibility to select files
	 *        manually from the computer.
	 *   responsiveness:
	 *     1: Standard dropzones SHOULD have a static height.
	 *
	 * ---
	 *
	 * @param string $url The url where the dropped files are being uploaded
	 * @return \ILIAS\UI\Component\Dropzone\File\Standard
	 */
	public function standard($url);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      A wrapper dropzone is used to display other ILIAS UI components
	 *      inside the dropzone. In contrast to the standard dropzone, the wrapper
	 *      dropzone is not visible by default. It gets highlighted once the
	 *      user is dragging files over the browser window.
	 *   composition: >
	 *      A wrapper dropzone contains one or multiple ILIAS UI components.
	 *      After dropping files, a roundtrip modal is opened which presents
	 *      the files to the user. The contains a button to start the upload
	 *      process.
	 *   effect: >
	 *      All wrapper dropzones on the page are highlighted when the user
	 *      dragging files over the browser window.
	 *   rivals:
	 *      Rival 1: >
	 *          A standard dropzone can display a message instead of other
	 *          ILIAS UI components.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages SHOULD NOT contain a wrapper dropzone.
	 *     2: Wrapper dropzones MUST contain one or more ILIAS UI components.
	 *     3: Wrapper dropzones MUST NOT contain any other file dropzones.
	 *     4: Wrapper dropzones MUST NOT be used in modals.
	 *   style:
	 *     1: >
	 *          The height and the width of a wrapper dropzone MUST
	 *          be determined by the components inside.
	 *
	 * ---
	 *
	 * @param string $url The url where the dropped files are being uploaded
	 * @param Component[]|Component $content Component(s) wrapped by the dropzone
	 * @return \ILIAS\UI\Component\Dropzone\File\Wrapper
	 */
	public function wrapper($url, $content);


}