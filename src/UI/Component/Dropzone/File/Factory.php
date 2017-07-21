<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * Interface Factory
 *
 * Describes a factory for file dropzones.
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
	 *      can be uploaded to the server. Standard dropzones CAN be used in
	 *      forms.
	 *   composition: >
	 *      Standard dropzones consist of a visible area where files can
	 *      be dropped. They MUST contain a message explaining that it is possible to
	 *      drop files inside. The dropped files are presented to the user along
	 *      with some button to start the upload process.
	 *   effect: >
	 *      A standard dropzone is highlighted when the user is dragging files
	 *      over the dropzone. After dropping, the dropped files are presented
	 *      to the user with some meta information of the files such the file name
	 *      and file size.
	 *   rivals:
	 *      Rival 1: >
	 *          A wrapper dropzone can hold other ILIAS UI components instead of
	 *          a message.
	 *
	 * rules:
	 *   usage:
	 *     1: A page SHOULD contain only one standard dropzone.
	 *     2: Standard dropzones MUST contain a message.
	 *     3: >
	 *        Standard dropzones MUST offer the possibility to select files
	 *        manually from the computer.
	 *     4: >
	 *        The upload button MUST be disabled if there are no files
	 *        to be uploaded.
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
	 *      inside it. In contrast to the standard dropzone, the wrapper
	 *      dropzone is not visible by default. Only the wrapped components are
	 *      visible. Any wrapper dropzone gets highlighted once the user is dragging
	 *      files over the browser window. Thus, a user needs to have the knowledge
	 *      that there are wrapper dropzones present.
	 *   composition: >
	 *      A wrapper dropzone contains one or multiple ILIAS UI components.
	 *      A roundtrip modal is used to present the dropped files and to initialize
	 *      the upload process.
	 *   effect: >
	 *      All wrapper dropzones on the page are highlighted when the user
	 *      dragging files over the browser window. After dropping the files, the
	 *      roundtrip modal is opened showing all files. The modal contains a button
	 *      to start the upload process.
	 *   rivals:
	 *      Rival 1: >
	 *          A standard dropzone displays a message instead of other
	 *          ILIAS UI components.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages SHOULD NOT contain a wrapper dropzone.
	 *     2: Wrapper dropzones MUST contain one or more ILIAS UI components.
	 *     3: Wrapper dropzones MUST NOT contain any other file dropzones.
	 *     4: Wrapper dropzones MUST NOT be used in modals.
	 *     5: >
	 *        The upload button in the modal MUST be disabled if there are no files
	 *        to be uploaded.
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