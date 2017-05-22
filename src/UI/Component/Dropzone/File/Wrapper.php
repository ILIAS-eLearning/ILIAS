<?php
/**
 * Interface Wrapper
 *
 * Describes a dropzone which can hold any other ILIAS UI components in it.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

interface Wrapper extends Dropzone {

	/**
	 * Clones this instance and sets the passed in argument on it.
	 *
	 * @param Component[]|Component $content an array or a single instance of ILIAS UI components
	 *
	 * @return Wrapper a copy of this instance
	 */
	public function withContent($content);


	/**
	 * @return Component[] an array of ILIAS UI components for this dropzone
	 */
	public function getContent();

}