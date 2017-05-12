<?php
/**
 * Class SimpleDropzone
 *
 * A simple wrapper class for a dropzone. Should only be used inside this namespace.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Implementation\Component\TriggeredSignalInterface;

class SimpleDropzone {

	/**
	 * @var string $id
	 */
	private $id;
	/**
	 * @var boolean $darkendBackground
	 */
	protected $darkendBackground;
	/**
	 * @var TriggeredSignalInterface[] $registeredSignals
	 */
	private $registeredSignals;


	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return bool
	 */
	public function isDarkendBackground() {
		return $this->darkendBackground;
	}


	/**
	 * @param bool $darkendBackground
	 */
	public function setDarkendBackground($darkendBackground) {
		$this->darkendBackground = $darkendBackground;
	}


	/**
	 * @return TriggeredSignalInterface[]
	 */
	public function getRegisteredSignals() {
		return $this->registeredSignals;
	}


	/**
	 * @param TriggeredSignalInterface[] $registeredSignals
	 */
	public function setRegisteredSignals(array $registeredSignals) {
		$this->registeredSignals = $registeredSignals;
	}

}