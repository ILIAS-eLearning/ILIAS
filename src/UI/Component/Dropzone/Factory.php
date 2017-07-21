<?php
/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI File Dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      File Dropzones are used to drop files from outside the browser window.
	 *      The dropped files are presented to the user and can be uploaded to the server.
	 *   composition: >
	 *      File Dropzones are areas to drop files being dragged from outside the browser window.
	 *   effect: >
	 *      A Dropzone is highlighted when the user drags files over it.
	 *
	 * rules:
	 *   usage:
	 *     1: Most pages SHOULD contain only one dropzone.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Dropzone\File\Factory
	 **/
	public function file();
}