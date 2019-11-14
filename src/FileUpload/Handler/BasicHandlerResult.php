<?php

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\HandlerResult;

/**
 * Class BasicHandlerResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicHandlerResult implements HandlerResult
{

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
    public function __construct(int $status, string $file_identifier, string $message)
    {
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
        return [
            'status'          => $this->status,
            'message'         => $this->message,
            'file_identifier' => $this->file_identifier,
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
