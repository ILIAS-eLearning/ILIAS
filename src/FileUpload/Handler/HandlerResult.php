<?php

namespace ILIAS\FileUpload\Handler;

use JsonSerializable;

/**
 * Interface UploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface HandlerResult extends JsonSerializable
{

    public const STATUS_OK = 1;
    public const STATUS_FAILED = 2;


    /**
     * @return int
     */
    public function getStatus() : int;


    /**
     * @return string
     */
    public function getFileIdentifier() : string;


    /**
     * @return string
     */
    public function getMessage() : string;
}
