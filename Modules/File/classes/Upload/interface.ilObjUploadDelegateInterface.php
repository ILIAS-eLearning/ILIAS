<?php

use ILIAS\FileUpload\DTO\UploadResult;

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

    public function getUploadedSuffixes() : array;
}
