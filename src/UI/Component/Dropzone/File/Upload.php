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

}