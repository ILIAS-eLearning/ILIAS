<?php
/**
 * Class Wrapper
 *
 * Implementation of a wrapper dropzone which can hold other ILIAS UI components.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

class Wrapper extends BasicFileDropzoneImpl implements \ILIAS\UI\Component\FileDropzone\Wrapper {

	private $componentList;


	/**
	 * Wrapper constructor.
	 *
	 * @param $componentList
	 */
	public function __construct(array $componentList) { $this->componentList = $componentList; }


	/**
	 * @inheritDoc
	 */
	function withContent(array $componentList) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->componentList = $componentList;
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	function getContent() {
		return $this->componentList;
	}
}