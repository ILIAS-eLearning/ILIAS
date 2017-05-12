<?php

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\Collection\StringMap;
use ILIAS\FileUpload\Exception\IllegalArgumentException;

/**
 * Class Metadata
 *
 * The meta data class holds all the data which are passed to each processor.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class Metadata {


	/**
	 * @var string $filename
	 */
	private $filename;
	/**
	 * @var UploadStatus
	 */
	private $status;
	/**
	 * @var int $size
	 */
	private $size;
	/**
	 * @var string $mimeType
	 */
	private $mimeType;
	/**
	 * @var StringMap $additionalMetaData
	 */
	private $additionalMetaData;


	/**
	 * Metadata constructor.
	 *
	 * @param string       $filename    The filename of the uploaded file.
	 * @param UploadStatus $status      The upload status.
	 * @param int          $size        The original size of the uploaded file.
	 * @param string       $mimeType    The mime type of the uploaded file.
	 *
	 * @throws IllegalArgumentException Thrown if the arguments are not matching with the expected types.
	 * @since 5.3
	 */
	public function __construct($filename, UploadStatus $status, $size, $mimeType) {

		$this->typeCheckFilename($filename);
		$this->typeCheckSize($size);
		$this->typeCheckMimeType($mimeType);

		if(!is_string($mimeType)) {
			$varType = gettype($mimeType);
			throw new IllegalArgumentException("The mimeType must be of type string but $varType was given.");
		}

		$this->filename = $filename;
		$this->status = $status;
		$this->size = $size;
		$this->mimeType = $mimeType;
		$this->additionalMetaData = new EntryLockingStringMap();
	}


	/**
	 * @return string
	 * @since 5.3
	 */
	public function getFilename() {
		return $this->filename;
	}


	/**
	 * @param string $filename
	 *
	 * @return Metadata
	 * @since 5.3
	 */
	public function setFilename($filename) {
		$this->typeCheckFilename($filename);

		$this->filename = $filename;

		return $this;
	}

	/**
	 * This always the original file size which was determinated by the http service.
	 *
	 * @return int
	 * @since 5.3
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * @return string
	 * @since 5.3
	 */
	public function getMimeType() {
		return $this->mimeType;
	}


	/**
	 * @param string $mimeType
	 *
	 * @return Metadata
	 * @since 5.3
	 */
	public function setMimeType($mimeType) {
		$this->typeCheckMimeType($mimeType);

		$this->mimeType = $mimeType;

		return $this;
	}


	/**
	 * Provides a string map implementation which allows the processors to store additional values.
	 * The string map implementation used by the meta data refuses to overwrite values.
	 *
	 * @return StringMap
	 * @since 5.3
	 */
	public function additionalMetaData() {
		return $this->additionalMetaData;
	}


	/**
	 * Type checking for the filename.
	 *
	 * @param string $filename The filename which should be checked.
	 *
	 * @throws IllegalArgumentException If the type of the filename is not equal to string.
	 */
	private function typeCheckFilename($filename) {
		if(!is_string($filename))
		{
			$varType = gettype($filename);
			throw new IllegalArgumentException("The filename must be of type string but $varType was given.");
		}
	}


	/**
	 * Type checking for the size.
	 *
	 * @param int $size The size which should be validated.
	 *
	 * @throws IllegalArgumentException If the type of the size is not equal to int.
	 */
	private function typeCheckSize($size) {
		if(!is_int($size))
		{
			$varType = gettype($size);
			throw new IllegalArgumentException("The size must be of type int but $varType was given.");
		}
	}

	/**
	 * Type checking for the mime type.
	 *
	 * @param string $mimeType The mime type which should be checked.
	 *
	 * @throws IllegalArgumentException If the data type of the mime type is not equal to string.
	 */
	private function typeCheckMimeType($mimeType) {
		if(!is_string($mimeType))
		{
			$varType = gettype($mimeType);
			throw new IllegalArgumentException("The mime type must be of type string but $varType was given.");
		}
	}
}