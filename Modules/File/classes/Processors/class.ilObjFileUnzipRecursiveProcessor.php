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

    public function process(ResourceIdentification $rid, array $options = []): void
    {
        $this->openZip($rid);

        // Create Base Container
        $zip_name = $this->storage->manage()->getCurrentRevision($rid)->getInformation()->getTitle();
        $info = new SplFileInfo($zip_name);
        $base_path = $info->getBasename("." . $info->getExtension());

        $base_container = $this->createContainerObj($base_path, $this->gui_object->getParentId());
        $this->path_map[$base_path] = (int) $base_container->getRefId();

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
                $this->createFileObj($rid, $parent_id_of_iteration, [], true);
            }
        }

        $this->closeZip();
    }
}
