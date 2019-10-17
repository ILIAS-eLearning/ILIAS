<?php

namespace ILIAS\FileUpload\Handler;

use ILIAS\Data\URI;

/**
 * Class UploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface UploadHandler
{

    /**
     * @return URI
     */
    public function getUploadURL() : string;


    public function executeCommand() : void;


    /**
     * @return HandlerResult
     */
    public function getResult() : HandlerResult;
}
