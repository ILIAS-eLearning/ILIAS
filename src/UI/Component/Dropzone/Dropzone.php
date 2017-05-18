<?php
/**
 * Interface Dropzone
 *
 * Describes a dropzone that can be configured to use the darkened background or not.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;

interface Dropzone extends Component, Droppable {

	/**
	 * Get a component like this, using the darkened background depending on the passed in argument.
	 *
	 * @param bool $useDarkenedBackground true to use the darkened background, otherwise false
	 *
	 * @return Dropzone a copy of this instance
	 */
	public function withDarkenedBackground($useDarkenedBackground);


	/**
	 * @return bool true if the darkened background is used, otherwise false
	 */
	public function isDarkenedBackground();

}