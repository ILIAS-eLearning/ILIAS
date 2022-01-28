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
 * Class ilObjFileUnzipRecursiveDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileUnzipRecursiveDelegate extends ilObjFileAbstractZipDelegate
{
    protected function getPossibleContainer(int $parent_id) : ilObject
    {
        if (!$this->isInWorkspace()) {
            $type = ilObject::_lookupType($parent_id, true);
        } else {
            $type = ilObject::_lookupType($this->tree->lookupObjectId($parent_id), false);
        }

        switch ($type) {
            // workspace structure
            case 'wfld':
            case 'wsrt':
                return new ilObjWorkspaceFolder();
            case 'cat':
            case 'root':
                return new ilObjCategory();
            case 'fold':
            case 'crs':
            default:
                return new ilObjFolder();
        }
    }

    /**
     * @param string $original_path
     * @param int    $parent_id
     * @return ilObject
     */
    protected function createContainer(string $original_path, int $parent_id) : ilObject
    {
        // Create folder or cat or WorkspaceFolder
        $obj = $this->getPossibleContainer($parent_id);
        $obj->setTitle(basename($original_path));
        $obj->create();

        if (!$this->isInWorkspace()) {
            $obj->createReference();
            $obj->putInTree($parent_id);
            $obj->setPermissions($parent_id);
        } else {
            $node_id = $this->tree->insertObject($parent_id, $obj->getId());
            $this->access_handler->setPermissions($parent_id, $node_id);
            $obj->setRefId($node_id);
        }

        if ($obj->getType() === "cat") {
            global $DIC;

            $lng = $DIC->language();
            $obj->addTranslation(basename($original_path), "", $lng->getLangKey(), $lng->getLangKey());
        }

        return $obj;
    }

    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse {
        $this->initZip($result);

        foreach ($this->getNextPath() as $original_path) {
            $is_dir = substr($original_path, -1) === DIRECTORY_SEPARATOR;
            $path = dirname($original_path);
            $parent_ref_id = isset($this->path_map[$path]) ? $this->path_map[$path] : $parent_id;
            if ($is_dir) {
                $obj = $this->createContainer($original_path, $parent_ref_id);
                $rtrim = rtrim($original_path, DIRECTORY_SEPARATOR);
                $i = $this->isInWorkspace() ? $obj->getId() : $obj->getRefId();
                $this->path_map[$rtrim] = $i;
            } else {
                $this->createFile($original_path, $parent_ref_id);
            }
        }

        $this->tearDown();

        $response = new ilObjFileUploadResponse();
        $response->error = null;
        return $response;
    }
}
