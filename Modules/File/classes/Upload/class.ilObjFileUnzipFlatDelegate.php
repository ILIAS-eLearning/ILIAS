<?php

use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class ilObjFileUnzipFlatDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileUnzipFlatDelegate extends ilObjFileAbstractZipDelegate
{

    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse {
        $this->initZip($result);

        foreach ($this->getNextPath() as $original_path) {
            $is_dir = substr($original_path, -1) === DIRECTORY_SEPARATOR;
            if ($is_dir) {
                continue;
            }
            $this->createFile($original_path, $parent_id);
        }

        $this->tearDown();

        $response = new ilObjFileUploadResponse();
        $response->error = null;
        return $response;
    }

}
