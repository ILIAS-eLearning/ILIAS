<?php
/**
 * Class SimpleDropzone
 *
 * A simple wrapper class for a dropzone. Should only be used inside this
 * namespace. Provides setter chaining.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.7
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\TriggeredSignalInterface;

class SimpleDropzone {

	/**
	 * @var string $id
	 */
	private $id;
	/**
	 * @var boolean $darkenedBackground
	 */
	protected $darkenedBackground;
	/**
	 * @var TriggeredSignalInterface[] $registeredSignals
	 */
	private $registeredSignals;
	/**
	 * @var string $type
	 */
	private $type;
	/**
	 * @var string $uploadUrl
	 */
	private $uploadUrl;
	/**
	 * @var string $uploadId
	 */
	private $uploadId;
	/**
	 * @var int
	 */
	private $uploadMaxFiles = 0;
	/**
	 * @var array
	 */
	private $uploadAllowedFileTypes = [];
	/**
	 * @var int
	 */
	private $uploadFileSizeLimit = 0;
	/**
	 * Private constructor. Initialize it through the static method
	 * {@link SimpleDropzone#of}.
	 * SimpleDropzone constructor.
	 */
	private function __construct() {
	}


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
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDarkenedBackground() {
		return $this->darkenedBackground;
	}


	/**
	 * @param bool $darkenedBackground
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setDarkenedBackground($darkenedBackground) {
		$this->darkenedBackground = $darkenedBackground;
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
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setRegisteredSignals(array $registeredSignals) {
		$this->registeredSignals = $registeredSignals;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param string $type
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->uploadUrl;
	}


	/**
	 * @param string $uploadUrl
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setUploadUrl($uploadUrl) {
		$this->uploadUrl = $uploadUrl;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getUploadId() {
		return $this->uploadId;
	}


	/**
	 * @param string $uploadId
	 *
	 * @return SimpleDropzone The instance of this object.
	 */
	public function setUploadId($uploadId) {
		$this->uploadId = $uploadId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getUploadMaxFiles() {
		return $this->uploadMaxFiles;
	}

	/**
	 * @param int $uploadMaxFiles
	 * @return SimpleDropzone
	 */
	public function setUploadMaxFiles($uploadMaxFiles) {
		$this->uploadMaxFiles = $uploadMaxFiles;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getUploadAllowedFileTypes() {
		return $this->uploadAllowedFileTypes;
	}

	/**
	 * @param array $uploadAllowedFileTypes
	 * @return SimpleDropzone
	 */
	public function setUploadAllowedFileTypes($uploadAllowedFileTypes) {
		$this->uploadAllowedFileTypes = $uploadAllowedFileTypes;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getUploadFileSizeLimit() {
		return $this->uploadFileSizeLimit;
	}

	/**
	 * @param int $uploadFileSizeLimit
	 * @return SimpleDropzone
	 */
	public function setUploadFileSizeLimit($uploadFileSizeLimit) {
		$this->uploadFileSizeLimit = $uploadFileSizeLimit;
		return $this;
	}
}