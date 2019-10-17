<?php

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\FileUpload\Handler\UploadHandler;

/**
 * This describes select field.
 */
interface File extends Input
{

    /**
     * @param array $mime_types
     *
     * @return File
     */
    public function withAcceptedMimeTypes(array $mime_types) : File;


    /**
     * @return array
     */
    public function getAcceptedMimeTypes() : array;


    /**
     * @param int $size_in_bytes
     *
     * @return File
     */
    public function withMaxFileSize(int $size_in_bytes) : File;


    /**
     * @return int
     */
    public function getMaxFileFize() : int;


    /**
     * @return UploadHandler
     */
    public function getUploadHandler() : UploadHandler;
}
