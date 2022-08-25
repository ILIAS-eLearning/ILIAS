<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;

/**
 * Class FileResourceHandler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface StorageHandler
{
    /**
     * @return string not longer than 8 characters
     */
    public function getID(): string;

    public function isPrimary(): bool;

    public function getIdentificationGenerator(): IdentificationGenerator;

    public function has(ResourceIdentification $identification): bool;

    public function getStream(Revision $revision): FileStream;

    public function storeUpload(UploadedFileRevision $revision): bool;

    public function storeStream(FileStreamRevision $revision): bool;

    public function cloneRevision(CloneRevision $revision): bool;

    /**
     * This only delets a revision of a Resource
     */
    public function deleteRevision(Revision $revision): void;

    /**
     * This deleted the whole container of a resource
     */
    public function deleteResource(StorableResource $resource): void;

    /**
     * This checks if there are empty directories in the filesystem which can be deleted. Currently only on first level.
     */
    public function cleanUpContainer(StorableResource $resource): void;

    /**
     * This is the place in the filesystem where the containers (nested) get created
     */
    public function getStorageLocationBasePath(): string;

    /**
     * This is the full path to the container of a ResourceIdentification (incl. StorageLocation base path).
     */
    public function getFullContainerPath(ResourceIdentification $identification): string;

    /**
     * This is only the path of a ResourceIdentification inside the StorageLocation base path
     */
    public function getContainerPathWithoutBase(ResourceIdentification $identification): string;

    /**
     * This is the full path to a revision of a Resource, incl. the StorageLocation base path. This can be used
     * to access the file itself. But getStream is musch easier for this.
     * @see getStream instead.
     */
    public function getRevisionPath(Revision $revision): string;

    /**
     * @return string "link" or "rename"
     */
    public function movementImplementation(): string;
}
