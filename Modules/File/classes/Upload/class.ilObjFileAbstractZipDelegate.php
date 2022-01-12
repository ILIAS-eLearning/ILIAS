<?php

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\Streams;

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
 * Class ilObjFileAbstractZipDelegate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilObjFileAbstractZipDelegate implements ilObjUploadDelegateInterface
{
    /**
     * @var ilAccess|ilWorkspaceAccessHandler
     */
    protected $access_handler;
    protected int $node_type;
    protected \ilTree $tree;
    protected array $path_map = [];
    protected ?\ZipArchive $zip = null;
    protected array $uploaded_suffixes = [];

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
        return $this->node_type === ilObject2GUI::WORKSPACE_NODE_ID;
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
     * @return \Iterator<string|bool>
     */
    protected function getNextPath() : \Iterator
    {
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $original_path = $this->zip->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            if (strpos($original_path, '__MACOSX') !== false || strpos($original_path, '.DS_') !== false) {
                continue;
            }
            yield $original_path;
        }
    }

    /**
     * @return string[]
     */
    public function getUploadedSuffixes() : array
    {
        return $this->uploaded_suffixes;
    }
}
