<?php

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * Class BasicFileInfoResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicFileInfoResult implements FileInfoResult
{

    /**
     * @var string
     */
    private $mime_type;
    /**
     * @var string
     */
    private $file_identifier;
    /**
     * @var int
     */
    private $size;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $file_identification_key;


    /**
     * BasicFileInfoResult constructor.
     *
     * @param string $file_identification_key
     * @param string $file_identifier
     * @param string $name
     * @param int    $size
     * @param string $mime_type
     */
    public function __construct(string $file_identification_key, string $file_identifier, string $name, int $size, string $mime_type)
    {
        $this->file_identification_key = $file_identification_key;
        $this->file_identifier = $file_identifier;
        $this->name = $name;
        $this->size = $size;
        $this->mime_type = $mime_type;
    }


    public function getFileIdentifier() : string
    {
        return $this->file_identifier;
    }


    public function getName() : string
    {
        return $this->name;
    }


    public function getSize() : int
    {
        return $this->size;
    }


    public function getMimeType() : string
    {
        return $this->mime_type;
    }


    /**
     * @inheritDoc
     */
    final public function jsonSerialize()
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
