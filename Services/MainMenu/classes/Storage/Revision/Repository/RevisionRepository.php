<?php

namespace ILIAS\MainMenu\Storage\Revision\Repository;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\MainMenu\Storage\Revision\Revision;
use ILIAS\MainMenu\Storage\Revision\RevisionCollection;
use ILIAS\MainMenu\Storage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Storage\StorableResource;

/**
 * Class RevisionARRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RevisionRepository
{

    /**
     * @param StorableResource $resource
     * @param UploadResult     $result
     *
     * @return UploadedFileRevision
     */
    public function blank(StorableResource $resource, UploadResult $result) : UploadedFileRevision;


    /**
     * @param Revision $revision
     */
    public function store(Revision $revision) : void;


    /**
     * @param StorableResource $resource
     *
     * @return RevisionCollection
     */
    public function get(StorableResource $resource) : RevisionCollection;


    /**
     * @param Revision $revision
     */
    public function delete(Revision $revision):void;
}