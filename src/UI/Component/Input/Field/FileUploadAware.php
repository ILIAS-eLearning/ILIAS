<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface FileUploadAware
{
    public function getUploadHandler() : UploadHandler;

    public function withMaxFileSize(int $size_in_bytes) : File;

    public function getMaxFileSize() : int;

    public function withMaxFiles(int $max_file_amount) : File;

    public function getMaxFiles() : int;

    /**
     * @param string[] $mime_types
     */
    public function withAcceptedMimeTypes(array $mime_types) : File;

    /**
     * @return string[]
     */
    public function getAcceptedMimeTypes() : array;

    public function hasZipOptions() : bool;
}