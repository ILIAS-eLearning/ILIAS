<?php

/**
 * Class ilObjFileUploadResponse
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileUploadResponse extends stdClass
{
    public $debug = '';
    public $error = null;
    public $fileName = '';
    public $fileSize = 0;
    public $fileType = '';

    public function send() : void
    {
        echo json_encode($this, JSON_THROW_ON_ERROR);
        // no further processing!
        exit;
    }
}
