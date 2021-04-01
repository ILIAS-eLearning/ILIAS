<?php

use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Interface ilObjUploadDelegateInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilObjUploadDelegateInterface
{
    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse;

//    public function getObjectId() : int;
}
