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
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileAbstractZipDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilObjFileAbstractZipDelegate implements ilObjUploadDelegateInterface
{
    /**
     * @var ilAccess|ilWorkspaceAccessHandler
     */
    protected $access_handler;
    /**
     * @var int
     */
    protected $node_type;
    /**
     * @var ilTree|ilWorkspaceTree
     */
    protected $tree;
    /**
     * @var array
     */
    protected $path_map = [];
    /**
     * @var ZipArchive
     */
    protected $zip;
    /**
     * @var array
     */
    protected $uploaded_suffixes = [];

    /**
     * @description Unzip on operating systems may behave differently when unzipping if there are only one or more root
     * nodes in the zip. Currently, the behavior is the same as in ILIAS 7 and earlier, that in any case the zip
     * structure is checked out directly at the current location in the magazine. If this value is set to true here,
     * another category/folder will be placed around the files and folders of the zip when unpacking if more than one
     * root-node is present. For example, macOS behaves in exactly this way.
     */
    protected $create_base_container_for_multiple_root_entries = false;

    /**
     * ilObjFileAbstractZipDelegate constructor.
     * @param ilAccess|ilWorkspaceAccessHandler $access_checker
     * @param int                               $node_type
     * @param ilTree                            $tree
     */
    public function __construct(object $access_checker, int $node_type, ilTree $tree)
    {
        $this->access_handler = $access_checker;
        $this->node_type = $node_type;
        $this->tree = $tree;
    }

    protected function createSurroundingContainer(UploadResult $result, int $parent_id) : int
    {
        // create one base container with the name of the zip itself, all other containers will be created inside this one
        $info = new SplFileInfo($result->getName());
        $base_path = $info->getBasename("." . $info->getExtension());
        $base_container = $this->createContainer($base_path, $parent_id);

        return (int) $base_container->getRefId();
    }



    protected function tearDown() : void
    {
        $this->zip->close();
    }

    /**
     * @param UploadResult $result
     */
    protected function initZip(UploadResult $result) : void
    {
        $this->zip = new ZipArchive();
        $this->zip->open($result->getPath());
    }


    protected function isInWorkspace() : bool
    {
        return $this->node_type === ilObjFileGUI::WORKSPACE_NODE_ID;
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


    /**
     * @param string $original_path
     * @param int    $parent_id
     * @return ilObjFile
     */
    protected function createFile(string $original_path, int $parent_id) : ilObjFile
    {
        $obj = new ilObjFile();
        $obj->setTitle(basename($original_path));
        $obj->create();

        $obj->appendStream(Streams::ofString($this->zip->getFromName($original_path)), basename($original_path));

        if (!$this->isInWorkspace()) {
            $obj->createReference();
            $obj->putInTree($parent_id);
            $obj->setPermissions($parent_id);
        } else {
            $node_id = $this->tree->insertObject($parent_id, $obj->getId());
            $this->access_handler->setPermissions($parent_id, $node_id);
            $obj->setRefId($node_id);
        }

        $this->uploaded_suffixes[] = $obj->getFileExtension();

        return $obj;
    }

    /**
     * @return Generator|string[]
     */
    protected function getNextPath() : Generator
    {
        $paths = [];

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $original_path = $this->zip->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            if (strpos($original_path, '__MACOSX') !== false || strpos($original_path, '.DS_') !== false) {
                continue;
            }
            $paths[] = $original_path;
        }

        $path_with_root_folders = [];

        foreach ($paths as $path) {
            $parent = dirname($path) . '/';
            if ($parent !== './' && !in_array($parent, $paths)) {
                $path_with_root_folders[] = $parent;
            }
            $path_with_root_folders[] = $path;
        }

        $path_with_root_folders = array_unique($path_with_root_folders);
        sort($path_with_root_folders);

        yield from $path_with_root_folders;
    }

    protected function hasMultipleRootEntriesInZip() : bool
    {
        $amount = 0;
        foreach ($this->getNextPath() as $zip_directory) {
            $dirname = dirname($zip_directory);
            if ($dirname === '.') {
                $amount++;
            }
            if ($amount > 1) {
                return true;
            }
        }
        return false;
    }

    public function getUploadedSuffixes() : array
    {
        return array_map('strtolower', $this->uploaded_suffixes);
    }
}
