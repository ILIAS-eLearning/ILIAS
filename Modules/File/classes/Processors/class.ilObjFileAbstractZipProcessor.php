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
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Services;

/**
 * Class ilObjFileAbstractZipProcessor
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilObjFileAbstractZipProcessor extends ilObjFileAbstractProcessor
{
    /**
     * @var string IRSS metadata key for filepath.
     */
    private const IRSS_FILEPATH_KEY = 'uri';

    /**
     * @var ilTree|ilWorkspaceTree
     */
    private $tree;

    private ?ZipArchive $archive = null;
    private int $id_type;

    /**
     * @description Unzip on operating systems may behave differently when unzipping if there are only one or more root
     * nodes in the zip. Currently, the behavior is the same as in ILIAS 7 and earlier, that in any case the zip
     * structure is checked out directly at the current location in the magazine. If this value is set to true here,
     * another category/folder will be placed around the files and folders of the zip when unpacking if more than one
     * root-node is present. For example, macOS behaves in exactly this way.
     */
    protected bool $create_base_container_for_multiple_root_entries = false;

    /**
     * @param ilTree|ilWorkspaceTree $tree
     */
    public function __construct(
        ResourceStakeholder $stakeholder,
        ilObjFileGUI $gui_object,
        Services $storage,
        ilFileServicesSettings $settings,
        $tree
    ) {
        parent::__construct($stakeholder, $gui_object, $storage, $settings);

        $this->id_type = $gui_object->getIdType();
        $this->tree = $tree;
    }

    protected function createSurroundingContainer(ResourceIdentification $rid): int
    {
        // create one base container with the name of the zip itself, all other containers will be created inside this one
        $zip_name = $this->storage->manage()->getCurrentRevision($rid)->getInformation()->getTitle();
        $info = new SplFileInfo($zip_name);
        $base_path = $info->getBasename("." . $info->getExtension());
        $base_container = $this->createContainerObj($base_path, $this->gui_object->getParentId());

        return (int) $base_container->getRefId();
    }

    /**
     * Creates a container object depending on the parent's node type and returns it.
     */
    protected function createContainerObj(string $dir_name, int $parent_id, array $options = []): ilObject
    {
        $container_obj = $this->getPossibleContainerObj($parent_id);
        $container_obj->setTitle($dir_name);

        if (!empty($options)) {
            $this->applyOptions($container_obj, $options);
        }

        $container_obj->create();
        $container_obj->createReference();

        $this->gui_object->putObjectInTree($container_obj, $parent_id);

        return $container_obj;
    }

    /**
     * Opens the zip archive of the given resource.
     */
    protected function openZip(ResourceIdentification $rid): void
    {
        if (null !== $this->archive) {
            throw new LogicException("openZip() can only be called once, yet it was called again.");
        }

        $file_uri = $this->storage->consume()->stream($rid)->getStream()->getMetadata(self::IRSS_FILEPATH_KEY);
        $this->archive = new ZipArchive();
        $this->archive->open($file_uri);
    }

    /**
     * Yields all paths of the currently open zip-archive (without some macos stuff).
     * @return Generator|string[]
     */
    private function getZipPaths(): Generator
    {
        if (null === $this->archive) {
            throw new LogicException("cannot read content of unopened zip archive");
        }

        for ($i = 0, $i_max = $this->archive->count(); $i < $i_max; $i++) {
            $path = $this->archive->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            if (strpos($path, '__MACOSX') !== false || strpos($path, '.DS_') !== false) {
                continue;
            }

            yield $path;
        }
    }

    /**
     * Yields the file-paths of the currently open zip-archive.
     * @return Generator|string[]
     */
    protected function getZipFiles(): Generator
    {
        foreach ($this->getZipPaths() as $path) {
            if (substr($path, -1) !== "/" && substr($path, -1) !== "\\") {
                yield $path;
            }
        }
    }

    protected function hasMultipleRootEntriesInZip(): bool
    {
        $amount = 0;
        foreach ($this->getZipDirectories() as $zip_directory) {
            $dirname = dirname($zip_directory);
            if ($dirname === '.') {
                $amount++;
            }
            if ($amount > 1) {
                return true;
            }
        }
        foreach ($this->getZipFiles() as $zip_file) {
            $dirname = dirname($zip_file);
            if ($dirname === '.') {
                $amount++;
            }
            if ($amount > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Yields the directory-paths of the currently open zip-archive.
     * This fixes the issue that win and mac zip archives have different directory structures.
     * @return Generator|string[]
     */
    protected function getZipDirectories(): Generator
    {
        $directories = [];
        foreach ($this->getZipPaths() as $path) {
            if (substr($path, -1) === "/" || substr($path, -1) === "\\") {
                $directories[] = $path;
            }
        }

        $directories_with_parents = [];

        foreach ($directories as $directory) {
            $parent = dirname($directory) . '/';
            if ($parent !== './' && !in_array($parent, $directories)) {
                $directories_with_parents[] = $parent;
            }
            $directories_with_parents[] = $directory;
        }

        $directories_with_parents = array_unique($directories_with_parents);
        sort($directories_with_parents);
        yield from $directories_with_parents;
    }


    /**
     * Creates an IRSS resource from the given filepath.
     */
    protected function storeZippedFile(string $file_path): ResourceIdentification
    {
        if (null === $this->archive) {
            throw new LogicException("No archive has been opened yet, call openZip() first in order to read files.");
        }

        return $this->storage->manage()->stream(
            Streams::ofString($this->archive->getFromName($file_path)),
            $this->stakeholder,
            basename($file_path)
        );
    }

    /**
     * Closes the currently open zip-archive.
     */
    protected function closeZip(): void
    {
        if ($this->archive !== null) {
            $this->archive->close();
        }
    }

    /**
     * Returns whether the current context is workspace.
     */
    protected function isWorkspace(): bool
    {
        return (ilObject2GUI::WORKSPACE_NODE_ID === $this->id_type);
    }

    /**
     * Returns a container object that is possible for the given parent.
     *
     * @param int $parent_id
     * @return ilObject
     */
    private function getPossibleContainerObj(int $parent_id): ilObject
    {
        $type = ($this->isWorkspace()) ?
            ilObject::_lookupType($this->tree->lookupObjectId($parent_id)) :
            ilObject::_lookupType($parent_id, true)
        ;

        switch ($type) {
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
}
