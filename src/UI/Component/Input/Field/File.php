<?php

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\FileUpload\Handler\UploadHandler;

/**
 * This describes select field.
 */
interface File extends Input
{

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
