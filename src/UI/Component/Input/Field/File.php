<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\FileUpload\Handler\ilCtrlAwareUploadHandler;

/**
 * This describes file field.
 */
interface File extends Input
{

    public function withAcceptedMimeTypes(array $mime_types) : File;


    public function getAcceptedMimeTypes() : array;


    public function withMaxFileSize(int $size_in_bytes) : File;


    public function getMaxFileFize() : int;


    public function getUploadHandler() : ilCtrlAwareUploadHandler;
}
