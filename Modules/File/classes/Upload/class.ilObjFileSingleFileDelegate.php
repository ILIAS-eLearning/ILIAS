<?php

use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class ilObjFileSingleFileDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileSingleFileDelegate implements ilObjUploadDelegateInterface
{
    /**
     * @var int
     */
    protected $object_id;

    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse {
        // Create new FileObject
        $file = new ilObjFile();
        $file->setTitle('pending...');
        $file->setDescription('pending...');
        $this->object_id = $file->create();
        $gui->putObjectInTree($file, $parent_id);
//        $gui->handleAutoRating($file);

        // Response
        $response = new ilObjFileUploadResponse();

        // Append Upload
        $title = $post_data['title'];
        $description = $post_data['description'];
        try {
            $file->appendUpload($result, $title ?? $result->getName());
            $file->setDescription($description);
            $file->update();
            $response->fileName = $file->getFileName();
            $response->fileSize = $file->getFileSize();
            $response->fileType = $file->getFileType();
            $response->error = null;
        } catch (Exception $e) {
            $file->delete();
            $response->error = $e->getMessage();
        }
        return $response;
    }
}
