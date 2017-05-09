<?php
/**
 * Class SimpleDropzone
 *
 * A simple wrapper class for a dropzone. Should only be used inside this namespace.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

class SimpleDropzone {

	/**
	 * @var string $id
	 */
	private $id;
	/**
	 * @var boolean $darkendBackground
	 */
	private $darkendBackground;


	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId(string $id) {
		$this->id = $id;
	}


	/**
	 * @return bool
	 */
	public function isDarkendBackground(): bool {
		return $this->darkendBackground;
	}


	/**
	 * @param bool $darkendBackground
	 */
	public function setDarkendBackground(bool $darkendBackground) {
		$this->darkendBackground = $darkendBackground;
	}

}