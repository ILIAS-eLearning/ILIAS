<?php
/**
 * Class Factory
 *
 * Default implementation for File Dropzone factory.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

class Factory implements \ILIAS\UI\Component\Dropzone\Factory {

	/**
	 * @inheritDoc
	 */
	public function file() {
		return new File\Factory();
	}
}