<?php
/**
 * Interface BasicFileDropzone
 *
 * Describes a file dropzone that can be configured to use the darkend background or not.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\FileDropzone
 */

namespace ILIAS\UI\Component\FileDropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;

interface BasicFileDropzone extends Component, Droppable {

	/**
	 * Clones this instance and sets the passed in argument on it.
	 *
	 * @param bool $useDarkendBackground true to use the darkend background, otherwise false
	 *
	 * @return BasicFileDropzone a copy of this instance
	 */
	function withDarkendBackground($useDarkendBackground);


	/**
	 * @return bool true if the darkend background is used, otherwise false
	 */
	function isDarkendBackground();

}