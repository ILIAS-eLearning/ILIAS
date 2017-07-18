<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;

/**
 * Interface File
 *
 * A dropzone where one can drop files on it to be uploaded on the server.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface File extends Component, Droppable {

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

	/**
	 * Get a dropzone like this, allowing to set the filename for each file being uploaded.
	 * The file name is sent as POST parameter along with the uploaded file.
	 *
	 * @param bool $state True to enable custom file names
	 * @return $this
	 */
	public function withCustomFileNames($state);

	/**
	 * Check if the dropzone supports to enter custom file names for each file being uploaded.
	 *
	 * @return bool
	 */
	public function allowCustomFileNames();

	/**
	 * Get a dropzone like this, allowing to set a description for each file being uploaded.
	 * The description is sent as POST parameter along with the uploaded file.
	 *
	 * @param bool $state True to enable custom file descriptions
	 * @return $this
	 */
	public function withFileDescriptions($state);

	/**
	 * Check if the dropzone supports to enter file descriptions for each file being uploaded.
	 *
	 * @return bool
	 */
	public function allowFileDescriptions();

	/**
	 * Get a dropzone like this where each uploaded file is identified over the given identifier
	 * via $_FILES[$identifier].
	 *
	 * By default, uploaded files are accessible via $_FILES['files'].
	 *
	 * @param string $identifier
	 * @return $this
	 */
	public function withIdentifier($identifier);

	/**
	 * Get the identifier used to retrieve the files server side via $_FILES.
	 *
	 * @return string
	 */
	public function getIdentifier();

}