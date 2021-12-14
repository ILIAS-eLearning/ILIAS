<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\FileUpload\Handler\FileInfoResult;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class FileUploadData
{
    protected FileInfoResult $file_info;
    protected bool $zip_extract;
    protected bool $keep_structure;

    /**
     * @var mixed
     */
    protected $metadata_value;

    /**
     * @param mixed $metadata_value
     */
    public function __construct(
        FileInfoResult $file_info,
        $metadata_value = null,
        bool $zip_extract = false,
        bool $keep_structure = false
    ) {
        $this->file_info = $file_info;
        $this->zip_extract = $zip_extract;
        $this->keep_structure = $keep_structure;
        $this->metadata_value = $metadata_value;
    }

    public function getFileId() : string
    {
        return $this->file_info->getFileIdentifier();
    }

    public function getName() : string
    {
        return $this->file_info->getName();
    }

    public function getSize() : int
    {
        return $this->file_info->getSize();
    }

    public function getMimeType() : string
    {
        return $this->file_info->getMimeType();
    }

    public function shouldExtract() : bool
    {
        return $this->zip_extract;
    }

    public function shouldKeepStructure() : bool
    {
        return $this->zip_extract && $this->keep_structure;
    }

    /**
     * @return mixed
     */
    public function getMetadata(bool $include_zip_options = false)
    {
        if (!$include_zip_options) {
            return $this->metadata_value;
        }

        return [
            [
                File::KEY_ZIP_EXTRACT => $this->zip_extract,
                File::KEY_ZIP_STRUCTURE => $this->keep_structure,
            ],
            $this->metadata_value,
        ];
    }
}