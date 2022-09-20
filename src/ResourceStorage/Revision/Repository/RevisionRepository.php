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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Revision\Repository;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * Class RevisionARRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface RevisionRepository extends LockingRepository, PreloadableRepository
{
    public function blankFromUpload(
        InfoResolver $info_resolver,
        StorableResource $resource,
        UploadResult $result
    ): UploadedFileRevision;

    public function blankFromStream(
        InfoResolver $info_resolver,
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false
    ): FileStreamRevision;

    public function blankFromClone(
        InfoResolver $info_resolver,
        StorableResource $resource,
        FileRevision $revision_to_clone
    ): CloneRevision;

    public function store(Revision $revision): void;

    public function get(StorableResource $resource): RevisionCollection;

    public function delete(Revision $revision): void;
}
