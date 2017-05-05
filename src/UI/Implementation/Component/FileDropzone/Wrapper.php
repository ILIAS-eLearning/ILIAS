<?php
/**
 * Class Wrapper
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\Component;

class Wrapper extends BasicFileDropzoneImpl implements \ILIAS\UI\Component\FileDropzone\Wrapper {

	private $componentList;

	/**
	 * @inheritDoc
	 */
	function withContent(array $componentList) {
		// TODO: Implement withContent() method.
	}


	/**
	 * @inheritDoc
	 */
	function getContent() {
		// TODO: Implement getContent() method.
	}
}