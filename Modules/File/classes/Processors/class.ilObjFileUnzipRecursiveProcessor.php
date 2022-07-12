<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileUnzipRecursiveProcessor
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjFileUnzipRecursiveProcessor extends ilObjFileAbstractZipProcessor
{
    /**
     * @var string[]
     */
    private array $path_map = [];

    public function process(ResourceIdentification $rid, array $options = []) : void
    {
        $this->openZip($rid);

        $first_dir = null;
        foreach ($this->getZipFiles() as $file_path) {
            $dir_name = dirname($file_path);
            $parent_id_of_iteration = (int) ($this->path_map[$dir_name] ?? $this->gui_object->getParentId());

            if (DIRECTORY_SEPARATOR === substr($file_path, -1)) {
                // only apply options for the first container object, which is
                // used for the container that represents the zip itself.
                if (null === $first_dir) {
                    $obj = $this->createContainerObj(basename($file_path), $parent_id_of_iteration, $options);
                    $first_dir = $file_path;
                } else {
                    $obj = $this->createContainerObj(basename($file_path), $parent_id_of_iteration);
                }

                // store the created container object id for possible sub-entries.
                $id = ($this->isWorkspace()) ? $obj->getId() : $obj->getRefId();
                $this->path_map[rtrim($file_path, DIRECTORY_SEPARATOR)] = $id;
            } else {
                $rid = $this->storeZippedFile($file_path);
                $this->createFileObj($rid, $parent_id_of_iteration);
            }
        }

        $this->closeZip();
    }
}
