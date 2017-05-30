<?php
/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI File Dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    22.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: File Dropzones are used to drop files from outside the
	 *   browser window. composition: > File Dropzones are areas to drop files
	 *   dragging from outside the browser window. effect: > A dropzone is
	 *   highlighted when the user drags files over it.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages SHOULD contain only one dropzone.
	 *     2: Dropzones MAY use the darkened background highlighting.
	 *     3: Other ILIAS UI components are REQUIRED to handle dropped files
	 *     further.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Dropzone\File\Factory
	 **/
	public function file();
}