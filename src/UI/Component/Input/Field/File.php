<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes file field.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface File
{
    /**
     * Returns the upload-handler, used to manage the uploaded files.
     *
     * @return UploadHandler
     */
    public function getUploadHandler() : UploadHandler;

    /**
     * Returns a file input like this, with mime-types that will be accepted.
     *
     * @param string[] $mime_types
     * @return File
     */
    public function withAcceptedMimeTypes(array $mime_types) : File;

    /**
     * Returns the mime-types that will be accepted by this input.
     *
     * @return string[]
     */
    public function getAcceptedMimeTypes() : ?array;

    /**
     * Returns a file input like this, with a max file-size.
     *
     * @param int $size_in_bytes
     * @return File
     */
    public function withMaxFileSize(int $size_in_bytes) : File;

    /**
     * Returns the max file-size accepted by this input.
     *
     * @return int
     */
    public function getMaxFileSize() : ?int;

    /**
     * Returns a file input like this, with a max amount of files that can be uploaded at once.
     *
     * @param int $amount
     * @return File
     */
    public function withMaxFiles(int $amount) : File;

    /**
     * Returns the max amount of files that can be uploaded at once.
     *
     * @return int
     */
    public function getMaxFiles() : int;

    /**
     * Returns a file input like this, with enabled or disabled zip-
     * extraction options.
     *
     * @param bool $with_options
     * @return FileInput
     */
    public function withZipExtractOptions(bool $with_options) : File;

    /**
     * Returns if the file input should have zip-extraction options.
     *
     * @return bool
     */
    public function hasZipExtractOptions() : bool;
}
