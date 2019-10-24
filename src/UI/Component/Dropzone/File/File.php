<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\Data\DataSize;
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
interface File extends Component, Droppable
{

    /**
     * Get a dropzone like this where the files are uploaded to the given URL.
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
     * Get a dropzone like this only accepting the submitted file types for uploading, e.g.
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
     * Get a dropzone like this, restricting the max number of files that can be uploaded.
     *
     * @param int $max
     * @return $this
     */
    public function withMaxFiles($max);

    /**
     * Get the max number of files that can be uploaded.
     *
     * @return int
     */
    public function getMaxFiles();

    /**
     * Get a dropzone like this, restricting the max file size of the files to the given limit.
     *
     * @param DataSize $limit
     * @return $this
     */
    public function withFileSizeLimit(DataSize $limit);

    /**
     * Get the max file size.
     *
     * @return DataSize
     */
    public function getFileSizeLimit();

    /**
     * Get a dropzone like this, allowing to set the filename for each file being uploaded.
     * The custom file name is sent as POST parameter along with the uploaded file.
     *
     * @param bool $state True to enable custom file names
     * @return $this
     */
    public function withUserDefinedFileNamesEnabled($state);

    /**
     * Check if the dropzone supports to enter custom file names for each file being uploaded.
     *
     * @return bool
     */
    public function allowsUserDefinedFileNames();

    /**
     * Get a dropzone like this, allowing to set a description for each file being uploaded.
     * The description is sent as POST parameter along with the uploaded file.
     *
     * @param bool $state True to enable file descriptions
     * @return $this
     */
    public function withUserDefinedDescriptionEnabled($state);

    /**
     * Check if the dropzone supports to enter file descriptions for each file being uploaded.
     *
     * @return bool
     */
    public function allowsUserDefinedFileDescriptions();

    /**
     * Get a dropzone like this where each uploaded file is identified over a given identifier.
     * The identifier corresponds to the key used to identify the files server side,
     * e.g. $_FILES[identifier]
     *
     * Note: If you use multiple file dropzones on the same page, you MUST use identifier in
     * order to identify an uploaded file. The default identifier is 'files'.
     *
     * @param string $parameter_name
     *
     * @return $this
     */
    public function withParameterName($parameter_name);

    /**
     * Get the identifier used to retrieve and identify an uploaded file server side.
     *
     * @return string
     */
    public function getParametername();
}
