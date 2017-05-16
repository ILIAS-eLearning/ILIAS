<?php
/**
 * Class SimpleDropzone
 *
 * A simple wrapper class for a dropzone. Should only be used inside this namespace.
 * Provides setter chaining.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.4
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
	 * @var boolean $useAutoHighlight
	 */
	private $useAutoHighlight;


	/**
	 * Private constructor. Initialize it through the static method {@link SimpleDropzone#of}.
	 * SimpleDropzone constructor.
	 */
	private function __construct() { }


	/**
	 * @return SimpleDropzone A new instance of a SimpleDropzone.
	 */
	public static function of() {
		return new SimpleDropzone();
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $id
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDarkendBackground() {
		return $this->darkendBackground;
	}


	/**
	 * @param bool $darkendBackground
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setDarkendBackground($darkendBackground) {
		$this->darkendBackground = $darkendBackground;
		return $this;
	}


	/**
	 * @return TriggeredSignalInterface[]
	 */
	public function getRegisteredSignals() {
		return $this->registeredSignals;
	}


	/**
	 * @param TriggeredSignalInterface[] $registeredSignals
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setRegisteredSignals(array $registeredSignals) {
		$this->registeredSignals = $registeredSignals;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isUseAutoHighlight(): bool {
		return $this->useAutoHighlight;
	}


	/**
	 * @param bool $useAutoHighlight
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setUseAutoHighlight(bool $useAutoHighlight) {
		$this->useAutoHighlight = $useAutoHighlight;
		return $this;
	}

}