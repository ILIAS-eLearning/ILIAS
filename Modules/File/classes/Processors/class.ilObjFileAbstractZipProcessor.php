<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

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
     * @var ZipArchive
     */
    private $archive;

    /**
     * @var int
     */
    private $node_id;

    /**
     * ilObjFileAbstractZipProcessor constructor.
     *
     * @param int                 $node_id
     *
     * @inheritDoc
     */
    public function __construct(ResourceStakeholder $stakeholder, ilObject2GUI $gui_object, $access_handler, $tree, int $node_id)
    {
        $this->node_id = $node_id;

        parent::__construct($stakeholder, $gui_object, $access_handler, $tree);
    }

    /**
     * Creates a container object depending on the parents node type and returns it.
     *
     * @param string $dir_name
     * @param int    $parent_id
     * @param array  $options
     * @return ilObject
     */
    protected function createContainerObj(string $dir_name, int $parent_id, array $options = []) : ilObject
    {
        $container_obj = $this->getPossibleContainerObj($parent_id);
        $container_obj->setTitle($dir_name);

        if (!empty($options)) {
            $this->applyOptions($container_obj, $options);
        }

        $container_obj->create();

        $this->gui_object->putObjectInTree($container_obj, $parent_id);

        return $container_obj;
    }

    /**
     * Opens the zip archive of the given resource.
     *
     * @param ResourceIdentification $rid
     */
    protected function openZip(ResourceIdentification $rid) : void
    {
        if (null !== $this->archive) {
            throw new LogicException("openZip() can only be called once, yet it was called again.");
        }

        $file_uri = $this->storage->consume()->stream($rid)->getStream()->getMetadata(self::IRSS_FILEPATH_KEY);
        $this->archive = new ZipArchive();
        $this->archive->open($file_uri);
    }

    /**
     * Yields the file-paths of the currently open zip-archive.
     *
     * @return Generator|string[]
     */
    protected function getZipFiles() : Generator
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
     * Creates an IRSS resource from the given filepath.
     *
     * @param string $file_path
     * @return ResourceIdentification
     */
    protected function storeZippedFile(string $file_path) : ResourceIdentification
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
    protected function closeZip() : void
    {
        if ($this->archive !== null) {
            $this->archive->close();
        }
    }

    /**
     * Returns whether or not the current context is workspace.
     *
     * @return bool
     */
    protected function isWorkspace() : bool
    {
        return (ilObject2GUI::WORKSPACE_NODE_ID === $this->node_id);
    }

    /**
     * Returns a container object that is possible for the given parent.
     *
     * @param int $parent_id
     * @return ilObject
     */
    private function getPossibleContainerObj(int $parent_id) : ilObject
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