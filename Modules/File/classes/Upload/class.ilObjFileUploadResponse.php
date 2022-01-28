<?php

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class ilObjFileUploadResponse
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileUploadResponse extends stdClass
{
    
    public string $debug = '';
    public ?string $error = null;
    public string $fileName = '';
    public int $fileSize = 0;
    public string $fileType = '';
    
    public function send() : void
    {
        echo json_encode($this, JSON_THROW_ON_ERROR);
        // no further processing!
        exit;
    }
}
