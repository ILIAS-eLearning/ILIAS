<?php declare(strict_types=1);

use ILIAS\FileUpload\DTO\UploadResult;

interface IndividualAssessmentFileStorage
{
    public function isEmpty() : bool;
    public function deleteCurrentFile() : void;
    public function getFilePath() : string;
    public function uploadFile(UploadResult $file) : bool;
    public function create() : void;
    public function setUserId(int $user_id) : void;
}
