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

use ILIAS\UI\Component\Component;

class Factory implements \ILIAS\UI\Component\Dropzone\File\Factory {

	/**
	 * @inheritdoc
	 */
	public function standard() {
		return new Standard();
	}

	/**
	 * @inheritdoc
	 */
	public function wrapper($content) {
		return new Wrapper($content);
	}

	/**
	 * @inheritdoc
	 */
	public function upload($content, $url) {
		return new Upload($content, $url);
	}

}