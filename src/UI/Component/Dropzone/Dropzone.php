<?php
/**
 * Interface Dropzone
 *
 * Describes a dropzone that can be configured to use the darkend background or not.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;

interface Dropzone extends Component, Droppable {

	/**
	 * Clones this instance and sets the passed in argument on it.
	 *
	 * @param bool $useDarkendBackground true to use the darkend background, otherwise false
	 *
	 * @return Dropzone a copy of this instance
	 */
	public function withDarkendBackground($useDarkendBackground);


	/**
	 * @return bool true if the darkend background is used, otherwise false
	 */
	public function isDarkendBackground();

}