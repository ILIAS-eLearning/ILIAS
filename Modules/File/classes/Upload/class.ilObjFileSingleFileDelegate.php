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
 * Class ilObjFileSingleFileDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileSingleFileDelegate implements ilObjUploadDelegateInterface
{
    protected ?int $object_id = null;
    
    protected array $uploaded_suffixes = [];

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

        $this->uploaded_suffixes[] = $file->getFileExtension();

        return $response;
    }

    /**
     * @return mixed[]
     */
    public function getUploadedSuffixes() : array
    {
        return $this->uploaded_suffixes;
    }
}
