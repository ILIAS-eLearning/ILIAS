<?php
/**
 * Class Factory
 *
 * Default implementation of the dropzone factory.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

class Factory implements \ILIAS\UI\Component\Dropzone\File\Factory {

	/**
	 * @inheritDoc
	 */
	public function standard() {
		return new Standard();
	}


	/**
	 * @inheritDoc
	 */
	public function wrapper($content) {
		return new Wrapper($content);
	}
}