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
        $base_node = $this->gui_object->getParentId();

        $multiple_root_entries = $this->hasMultipleRootEntriesInZip();

        // Create Base Container if needed
        if ($this->create_base_container_for_multiple_root_entries && $multiple_root_entries) {
            $base_node = $this->createSurroundingContainer($rid);
        }

        $this->path_map['./'] = $base_node;

        // Create Containers first to have proper path mapping after,
        // differences between macOS and windows are already handled in getZipDirectories()
        foreach ($this->getZipDirectories() as $directory) {
            $dir_name = dirname($directory) . '/';
            $parent_id_of_iteration = (int) ($this->path_map[$dir_name] ?? $base_node);

            $obj = $this->createContainerObj(basename($directory), $parent_id_of_iteration);
            $this->path_map[$directory] = (int) $obj->getRefId();
        }


        // Create Files
        foreach ($this->getZipFiles() as $file_path) {
            $dir_name = dirname($file_path) . '/';
            $parent_id_of_iteration = (int) ($this->path_map[$dir_name] ?? $base_node);

            $this->createFileObj($this->storeZippedFile($file_path), $parent_id_of_iteration, [], true);
        }

        $this->closeZip();
    }
}
