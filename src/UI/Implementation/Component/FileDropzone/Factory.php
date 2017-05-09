<?php
/**
 * Class Factory
 *
 * Default implementation of the dropzone factory.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

class Factory implements \ILIAS\UI\Component\FileDropzone\Factory {

	/**
	 * @inheritDoc
	 */
	public function standard() {
		return new Standard();
	}


	/**
	 * @inheritDoc
	 */
	public function wrapper(array $componentList) {
		return new Wrapper($componentList);
	}
}