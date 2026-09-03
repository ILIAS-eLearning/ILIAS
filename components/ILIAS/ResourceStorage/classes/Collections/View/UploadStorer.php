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

declare(strict_types=1);

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * Stores a single successful upload result into a resource collection, applying
 * the collection's OnDuplicate strategy when a resource with the same name
 * already exists in that collection.
 *
 * This deliberately holds no HTTP/GUI dependencies so that the duplicate
 * behaviour can be unit tested in isolation (see UploadStorerTest).
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class UploadStorer
{
    public function __construct(
        private Manager $manage,
        private Collections $collections
    ) {
    }

    /**
     * @return ResourceIdentification|null the identification of the affected
     *         resource, or null if the upload was rejected (OnDuplicate::REJECT)
     *         and therefore not stored.
     */
    public function store(
        ResourceCollection $collection,
        ResourceStakeholder $stakeholder,
        OnDuplicate $on_duplicate,
        UploadResult $result
    ): ?ResourceIdentification {
        $existing_rid = $this->findResourceToWriteTo($collection, $on_duplicate, $result->getName());

        if ($existing_rid === null) {
            // no name clash (or duplicates allowed): store as a new, separate resource
            $rid = $this->manage->upload($result, $stakeholder);
            $collection->add($rid);
            return $rid;
        }

        switch ($on_duplicate) {
            case OnDuplicate::REJECT:
                // leave the existing resource untouched, do not store the upload
                return null;
            case OnDuplicate::REPLACE:
                // overwrite with a new revision and drop all previous revisions
                $this->manage->replaceWithUpload($existing_rid, $result, $stakeholder);
                return $existing_rid;
            case OnDuplicate::APPEND_REVISION:
                // overwrite by appending a new revision while keeping the previous ones as history
                $this->manage->appendNewRevision($existing_rid, $result, $stakeholder);
                return $existing_rid;
        }

        // OnDuplicate::ALLOW never reaches this point ($existing_rid is null above)
        return $existing_rid;
    }

    /**
     * The twin of store() for an upload that did not arrive as one request and
     * therefore has no UploadResult: a chunked upload is reassembled into a file
     * of its own and handed over as a stream. The duplicate behaviour is the
     * same, only the way the resource is written differs.
     *
     * @param string $file_name the name the file was uploaded under - it decides
     *        what counts as a duplicate, and the stream alone does not carry it
     * @return ResourceIdentification|null the identification of the affected
     *         resource, or null if the upload was rejected (OnDuplicate::REJECT)
     *         and therefore not stored.
     */
    public function storeStream(
        ResourceCollection $collection,
        ResourceStakeholder $stakeholder,
        OnDuplicate $on_duplicate,
        FileStream $stream,
        string $file_name
    ): ?ResourceIdentification {
        $existing_rid = $this->findResourceToWriteTo($collection, $on_duplicate, $file_name);

        if ($existing_rid === null) {
            // no name clash (or duplicates allowed): store as a new, separate resource
            $rid = $this->manage->stream($stream, $stakeholder, $file_name);
            $collection->add($rid);
            return $rid;
        }

        switch ($on_duplicate) {
            case OnDuplicate::REJECT:
                // leave the existing resource untouched, do not store the upload
                return null;
            case OnDuplicate::REPLACE:
                // overwrite with a new revision and drop all previous revisions
                $this->manage->replaceWithStream($existing_rid, $stream, $stakeholder, $file_name);
                return $existing_rid;
            case OnDuplicate::APPEND_REVISION:
                // overwrite by appending a new revision while keeping the previous ones as history
                $this->manage->appendNewRevisionFromStream($existing_rid, $stream, $stakeholder, $file_name);
                return $existing_rid;
        }

        // OnDuplicate::ALLOW never reaches this point ($existing_rid is null above)
        return $existing_rid;
    }

    /**
     * @return ResourceIdentification|null the resource already holding that name,
     *         or null if there is none - ALLOW never dedupes and must not even ask.
     */
    private function findResourceToWriteTo(
        ResourceCollection $collection,
        OnDuplicate $on_duplicate,
        string $file_name
    ): ?ResourceIdentification {
        // an existing resource with the same name is only relevant if duplicates
        // are not simply allowed
        return $on_duplicate === OnDuplicate::ALLOW
            ? null
            : $this->collections->findIdentificationByNameIn($collection, $file_name);
    }
}
