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

    public function handle(
        int $parent_id,
        array $post_data,
        UploadResult $result,
        ilObjFileGUI $gui
    ) : ilObjFileUploadResponse {
        $this->initZip($result);

        $base_node_id = $parent_id;

        // Create Base Container if needed
        if ($this->create_base_container_for_multiple_root_entries && $this->hasMultipleRootEntriesInZip()) {
            $base_node_id = $this->createSurroundingContainer($result, $parent_id);
        }

        $this->path_map['./'] = $base_node_id;

        foreach ($this->getNextPath() as $original_path) {
            $dir_name = dirname($original_path) . '/';
            $parent_id_of_iteration = (int) ($this->path_map[$dir_name] ?? $parent_id);
            $is_dir = substr($original_path, -1) === DIRECTORY_SEPARATOR;

            if ($is_dir) {
                $obj = $this->createContainer($original_path, $parent_id_of_iteration);
                $i = $this->isInWorkspace() ? $obj->getRefId() : $obj->getRefId();
                $this->path_map[$original_path] = (int) $i;
            } else {
                $this->createFile($original_path, $parent_id_of_iteration);
            }
        }

        $this->tearDown();

        $response = new ilObjFileUploadResponse();
        $response->error = null;
        return $response;
    }
}
