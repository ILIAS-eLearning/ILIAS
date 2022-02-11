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
 * Class BasicHandlerResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicHandlerResult implements HandlerResult
{
    private string $file_identification_key;
    private int $status;
    private string $file_identifier;
    private string $message;


    /**
     * BasicHandlerResult constructor.
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
    final public function jsonSerialize() : array
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
