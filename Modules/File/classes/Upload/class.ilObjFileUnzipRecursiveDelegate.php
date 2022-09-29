<?php

use ILIAS\FileUpload\DTO\UploadResult;

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

        // Create Base Container
        $info = new SplFileInfo($result->getName());
        $base_path = $info->getBasename("." . $info->getExtension());
        $base_container = $this->createContainer($base_path, $parent_id);
        $this->path_map[$base_path] = (int) $base_container->getRefId();

        foreach ($this->getNextPath() as $original_path) {
            $is_dir = substr($original_path, -1) === DIRECTORY_SEPARATOR;
            $path = dirname($original_path);
            $rtrim = rtrim($original_path, DIRECTORY_SEPARATOR);
            if ($rtrim === $base_path) {
                continue;
            }
            $parent_ref_id = isset($this->path_map[$path]) ? $this->path_map[$path] : $parent_id;
            if ($is_dir) {
                $obj = $this->createContainer($original_path, $parent_ref_id);
                $i = $this->isInWorkspace() ? $obj->getRefId() : $obj->getRefId();
                $this->path_map[$rtrim] = (int) $i;
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
