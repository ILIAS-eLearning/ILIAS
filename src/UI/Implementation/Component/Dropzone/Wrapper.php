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
 * @package ILIAS\UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Wrapper extends Dropzone implements \ILIAS\UI\Component\Dropzone\Wrapper {
	use ComponentHelper;

	/**
	 * @var Component[]
	 */
	private $componentList;

	/**
	 * Wrapper constructor.
	 * An array of ILIAS UI components. At least, the array must contain one or more elements.
	 *
	 * @param Component[]|Component $content an array or a single instance of ILIAS UI components
	 */
	public function __construct($content) {
		$this->componentList = $this->toArray($content);
		$types = array(Component::class);
		$this->checkArgListElements('content', $content, $types);
		$this->checkEmptyArray($this->componentList);
		$this->darkenedBackground = true;
	}


	/**
	 * @inheritDoc
	 */
	public function withContent($content) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->componentList = $this->toArray($content);
		$this->checkEmptyArray($clonedFileDropzone->componentList);
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent() {
		return $this->componentList;
	}


	/**
	 * Checks the size of the passed in argument to 0.
	 *
	 * @param array $array the array to check
	 * @throws \LogicException if the passed in argument counts 0
	 */
	private function checkEmptyArray(array $array) {
		if (count($array) === 0) {
			throw new \LogicException("At least, one ILIAS UI component is required, otherwise this element is not visible.");
		}
	}

}