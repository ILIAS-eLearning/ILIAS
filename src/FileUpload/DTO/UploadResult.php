<?php

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\Collection\ImmutableStringMap;

/**
 * Class UploadResult
 *
 * The upload results are used to tell ILIAS about the file uploads.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class UploadResult {

	/**
	 * @var string $name
	 */
	private $name;
	/**
	 * @var int $size
	 */
	private $size;
	/**
	 * @var string $mimeType
	 */
	private $mimeType;
	/**
	 * @var ImmutableStringMap $metaData
	 */
	private $metaData;
	/**
	 * @var int $status
	 */
	private $status;
	/**
	 * @var string $statusMessage
	 */
	private $statusMessage;
	/**
	 * @var string $path
	 */
	private $path;


	/**
	 * UploadResult constructor.
	 *
	 * @param string             $name              The name of the uploaded file.
	 * @param int                $size              The original file size.
	 * @param string             $mimeType          The mime type of the uploaded file.
	 * @param ImmutableStringMap $metaData          Additional meta data. Make sure to wrap the instance with an ImmutableMapWrapper if the instance is mutable.
	 * @param int                $status            The status code either OK or REJECTED.
	 * @param string             $statusMessage     The additional message why the specific status got set.
	 * @param string             $path              The path to the newly moved file.
	 */
	public function __construct($name, $size, $mimeType, ImmutableStringMap $metaData, $status, $statusMessage, $path) {
		$this->name = $name;
		$this->size = $size;
		$this->mimeType = $mimeType;
		$this->metaData = $metaData;
		$this->status = $status;
		$this->statusMessage = $statusMessage;
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * @return string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}


	/**
	 * @return ImmutableStringMap
	 */
	public function getMetaData() {
		return $this->metaData;
	}


	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @return string
	 */
	public function getStatusMessage() {
		return $this->statusMessage;
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}