<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes file field.
 */
interface File extends FormInput
{
    public function withAcceptedMimeTypes(array $mime_types) : File;

    public function getAcceptedMimeTypes() : array;

    public function withMaxFileSize(int $size_in_bytes) : File;

    public function getMaxFileFize() : int;

    public function getUploadHandler() : UploadHandler;
}
