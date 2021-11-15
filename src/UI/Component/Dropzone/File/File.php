<?php declare(strict_types=1);

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
     */
    public function withUploadUrl(string $url) : File;

    /**
     * Get the upload URL where the files are uploaded.
     */
    public function getUploadUrl() : string;

    /**
     * Get a dropzone like this only accepting the submitted file types for uploading, e.g.
     * ['jpg', 'png', 'gif'] to allow some image formats.
     */
    public function withAllowedFileTypes(array $types) : File;

    public function getAllowedFileTypes() : array;

    /**
     * Get a dropzone like this, restricting the max number of files that can be uploaded.
     */
    public function withMaxFiles(int $max) : File;

    /**
     * Get the max number of files that can be uploaded.
     */
    public function getMaxFiles() : int;

    /**
     * Get a dropzone like this, restricting the max file size of the files to the given limit.
     */
    public function withFileSizeLimit(DataSize $limit) : File;

    /**
     * Get the max file size.
     */
    public function getFileSizeLimit() : ?DataSize;

    /**
     * Get a dropzone like this, allowing to set the filename for each file being uploaded.
     * The custom file name is sent as POST parameter along with the uploaded file.
     *
     * @param bool $state True to enable custom file names
     */
    public function withUserDefinedFileNamesEnabled(bool $state) : File;

    /**
     * Check if the dropzone supports to enter custom file names for each file being uploaded.
     */
    public function allowsUserDefinedFileNames() : bool;

    /**
     * Get a dropzone like this, allowing to set a description for each file being uploaded.
     * The description is sent as POST parameter along with the uploaded file.
     *
     * @param bool $state True to enable file descriptions
     */
    public function withUserDefinedDescriptionEnabled(bool $state) : File;

    /**
     * Check if the dropzone supports to enter file descriptions for each file being uploaded.
     */
    public function allowsUserDefinedFileDescriptions() : bool;

    /**
     * Get a dropzone like this where each uploaded file is identified over a given identifier.
     * The identifier corresponds to the key used to identify the files server side,
     * e.g. $_FILES[identifier]
     *
     * Note: If you use multiple file dropzones on the same page, you MUST use identifier in
     * order to identify an uploaded file. The default identifier is 'files'.
     */
    public function withParameterName(string $parameter_name) : File;

    /**
     * Get the identifier used to retrieve and identify an uploaded file server side.
     */
    public function getParameterName() : string;
}
