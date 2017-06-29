<?php

namespace ILIAS\UI\Component\Dropzone\File;

/**
 * A wrapper dropzone displays the dropped files in a modal which offers a button to upload the files.
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Upload extends Wrapper {

	/**
	 * Get an upload wrapper like this where the files are uploaded to the given URL.
	 *
	 * @param string $url
	 * @return $this
	 */
	public function withUploadUrl($url);

	/**
	 * Get the upload URL where the files are uploaded.
	 *
	 * @return string
	 */
	public function getUploadUrl();

	/**
	 * Get an upload wrapper like this only accepting the submitted file types for uploading, e.g.
	 * ['jpg', 'png', 'gif'] to allow some image formats.
	 *
	 * @param array $types
	 * @return $this
	 */
	public function withAllowedFileTypes(array $types);

	/**
	 * @return array
	 */
	public function getAllowedFileTypes();

	/**
	 * Get an upload wrapper like this, restricting the max number of files that can be uploaded.
	 *
	 * @param int $max
	 * @return $this
	 */
	public function withMaxFiles($max);

	/**
	 * @return int
	 */
	public function getMaxFiles();

	/**
	 * Get an upload wrapper like this, restricting the max file size of the files to the given limit (in bytes).
	 *
	 * @param int $limit Max size for any file uploaded in bytes
	 * @return $this
	 */
	public function withFileSizeLimit($limit);

	/**
	 * @return int
	 */
	public function getFileSizeLimit();
}