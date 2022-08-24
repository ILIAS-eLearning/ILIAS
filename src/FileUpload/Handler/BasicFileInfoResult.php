<?php

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class BasicFileInfoResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicFileInfoResult implements FileInfoResult
{
    private string $mime_type;
    private string $file_identifier;
    private int $size;
    private string $name;
    private string $file_identification_key;


    /**
     * BasicFileInfoResult constructor.
     */
    public function __construct(string $file_identification_key, string $file_identifier, string $name, int $size, string $mime_type)
    {
        $this->file_identification_key = $file_identification_key;
        $this->file_identifier = $file_identifier;
        $this->name = $name;
        $this->size = $size;
        $this->mime_type = $mime_type;
    }


    public function getFileIdentifier(): string
    {
        return $this->file_identifier;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getSize(): int
    {
        return $this->size;
    }


    public function getMimeType(): string
    {
        return $this->mime_type;
    }


    /**
     * @inheritDoc
     */
    final public function jsonSerialize(): array
    {
        $str = $this->file_identification_key ?? UploadHandler::DEFAULT_FILE_ID_PARAMETER;

        return [
            'name' => $this->name,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            $str => $this->file_identifier,
        ];
    }
}
