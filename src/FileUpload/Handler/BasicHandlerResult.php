<?php

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * Class BasicHandlerResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicHandlerResult implements HandlerResult
{

    /**
     * @var string
     */
    private $file_identification_key;
    /**
     * @var int
     */
    private $status;
    /**
     * @var string
     */
    private $file_identifier;
    /**
     * @var string
     */
    private $message;


    /**
     * BasicHandlerResult constructor.
     *
     * @param int    $status
     * @param string $file_identifier
     * @param string $message
     */
    public function __construct(string $file_identification_key, int $status, string $file_identifier, string $message)
    {
        $this->file_identification_key = $file_identification_key;
        $this->status = $status;
        $this->file_identifier = $file_identifier;
        $this->message = $message;
    }


    /**
     * @inheritDoc
     */
    public function getStatus() : int
    {
        return $this->status;
    }


    /**
     * @inheritDoc
     */
    public function getFileIdentifier() : string
    {
        return $this->file_identifier;
    }


    /**
     * @inheritDoc
     */
    final public function jsonSerialize()
    {
        $str = $this->file_identification_key ?? UploadHandler::DEFAULT_FILE_ID_PARAMETER;

        return [
            'status' => $this->status,
            'message' => $this->message,
            $str => $this->file_identifier,
        ];
    }


    /**
     * @inheritDoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }
}
