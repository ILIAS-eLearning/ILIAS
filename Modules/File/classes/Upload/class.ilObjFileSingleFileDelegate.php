<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    /**
     * @var array
     */
    protected $uploaded_suffixes = [];

    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse {
        // Create new FileObject
        $file = new ilObjFile();
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
            $this->uploaded_suffixes[] = $file->getFileExtension();
        } catch (Exception $e) {
            $file->delete();
            $response->error = $e->getMessage();
        }

        return $response;
    }

    public function getUploadedSuffixes() : array
    {
        return array_map('strtolower', $this->uploaded_suffixes);
    }
}
