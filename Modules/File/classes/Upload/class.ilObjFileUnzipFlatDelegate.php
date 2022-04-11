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
