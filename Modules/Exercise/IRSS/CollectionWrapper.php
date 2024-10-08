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

namespace ILIAS\Exercise\IRSS;

use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\InternalDataService;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\LegacyPathHelper;

class CollectionWrapper
{
    protected InternalDataService $data;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected \ILIAS\ResourceStorage\Services $irss;

    public function __construct(
        InternalDataService $data
    ) {
        global $DIC;

        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
        $this->data = $data;
    }

    protected function getNewCollectionId(): ResourceCollectionIdentification
    {
        return $this->irss->collection()->id();
    }

    protected function getNewCollectionIdAsString(): string
    {
        return $this->getNewCollectionId()->serialize();
    }

    public function createEmptyCollection(): string
    {
        $new_id = $this->getNewCollectionId();
        $new_collection = $this->irss->collection()->get($new_id);
        $this->irss->collection()->store($new_collection);
        return $new_id->serialize();
    }

    public function getCollectionForIdString(string $rcid): ResourceCollection
    {
        return $this->irss->collection()->get($this->irss->collection()->id($rcid));
    }

    public function deleteCollectionForIdString(
        string $rcid,
        ResourceStakeholder $stakeholder
    ): void {
        $id = $this->irss->collection()->id($rcid);
        $this->irss->collection()->remove($id, $stakeholder, true);
    }

    public function copyResourcesToDir(
        string $rcid,
        ResourceStakeholder $stakeholder,
        string $dir
    ) {
        $collection = $this->irss->collection()->get($this->irss->collection()->id($rcid));
        foreach ($collection->getResourceIdentifications() as $rid) {
            $info = $this->irss->manage()->getResource($rid)
                               ->getCurrentRevision()
                               ->getInformation();
            $stream = $this->irss->consume()->stream($rid);
            $stream->getContents();
        }
    }

    public function importFilesFromLegacyUploadToCollection(
        ResourceCollection $collection,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): void {
        $upload = $this->upload;

        if (is_array($file_input)) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            foreach ($upload->getResults() as $name => $result) {
                // we must check if these are files from this input
                if (!in_array($name, $file_input["tmp_name"] ?? [], true)) {
                    continue;
                }
                // if the result is not OK, we skip it
                if (!$result->isOK()) {
                    continue;
                }

                // we store the file in the IRSS
                $rid = $this->irss->manage()->upload(
                    $result,
                    $stakeholder
                );
                // and add its identification to the collection
                $collection->add($rid);
            }
            // we store the collection after all files have been added
            $this->irss->collection()->store($collection);
        }
    }

    public function importFilesFromDirectoryToCollection(
        ResourceCollection $collection,
        string $directory,
        ResourceStakeholder $stakeholder
    ): void {
        $sourceFS = LegacyPathHelper::deriveFilesystemFrom($directory);
        $sourceDir = LegacyPathHelper::createRelativePath($directory);

        // check if arguments are directories
        if (!$sourceFS->hasDir($sourceDir)) {
            return;
        }

        $sourceList = $sourceFS->listContents($sourceDir, false);

        foreach ($sourceList as $item) {
            if ($item->isDir()) {
                continue;
            }
            try {
                $stream = $sourceFS->readStream($item->getPath());
                $rid = $this->irss->manage()->stream(
                    $stream,
                    $stakeholder
                );
                $collection->add($rid);
            } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
            }
        }
        $this->irss->collection()->store($collection);
    }

    protected function getResourceIdForIdString(string $rid): ?ResourceIdentification
    {
        return $this->irss->manage()->find($rid);
    }

    public function importFileFromLegacyUpload(
        array $file_input,
        ResourceStakeholder $stakeholder
    ): string {
        $upload = $this->upload;

        if (is_array($file_input)) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            foreach ($upload->getResults() as $name => $result) {
                // we must check if these are files from this input
                if ($name !== ($file_input["tmp_name"] ?? "")) {
                    continue;
                }
                // if the result is not OK, we skip it
                if (!$result->isOK()) {
                    continue;
                }

                // we store the file in the IRSS
                $rid = $this->irss->manage()->upload(
                    $result,
                    $stakeholder
                );
                return $rid->serialize();
            }
        }
        return "";
    }

    public function importFileFromUploadResult(
        UploadResult $result,
        ResourceStakeholder $stakeholder
    ): string {
        // if the result is not OK, we skip it
        if (!$result->isOK()) {
            return "";
        }

        // we store the file in the IRSS
        $rid = $this->irss->manage()->upload(
            $result,
            $stakeholder
        );
        return $rid->serialize();
    }

    public function deliverFile(string $rid): void
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            $this->irss->consume()->download($id)->run();
        }
    }

    public function stream(string $rid): ?FileStream
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            return $this->irss->consume()->stream($id)->getStream();
        }
        return null;
    }

    public function getCollectionResourcesInfo(
        ResourceCollection $collection
    ): \Generator {
        foreach ($collection->getResourceIdentifications() as $rid) {
            $info = $this->irss->manage()->getResource($rid)
                               ->getCurrentRevision()
                               ->getInformation();
            $src = $this->irss->consume()->src($rid)->getSrc();
            yield $this->data->resourceInformation(
                $rid->serialize(),
                $info->getTitle(),
                $info->getSize(),
                $info->getCreationDate()->getTimestamp(),
                $info->getMimeType(),
                $src
            );
        }
    }

    public function clone(
        string $from_rc_id
    ): string {
        if ($from_rc_id !== "") {
            $cloned_rcid = $this->irss->collection()->clone($this->irss->collection()->id($from_rc_id));
            return $cloned_rcid->serialize();
        }
        return "";
    }

    public function cloneResource(
        string $from_rid
    ): string {
        if ($from_rid !== "") {
            $cloned_rid = $this->irss->manage()->clone($this->getResourceIdForIdString($from_rid));
            return $cloned_rid->serialize();
        }
        return "";
    }

    public function deleteResource(string $rid, ResourceStakeholder $stakeholder): void
    {
        if ($rid !== "") {
            $res = $this->getResourceIdForIdString($rid);
            if ($res) {
                $this->irss->manage()->remove($this->getResourceIdForIdString($rid), $stakeholder);
            }
        }
    }

    public function addEntryOfZipResourceToCollection(
        string $rid,
        string $entry,
        ResourceCollection $target_collection,
        ResourceStakeholder $target_stakeholder
    ) {
        $entry_parts = explode("/", $entry);
        $zip_path = $this->stream($rid)->getMetadata("uri");

        $stream = Streams::ofFileInsideZIP(
            $zip_path,
            $entry
        );
        $feedback_rid = $this->irss->manage()->stream(
            $stream,
            $target_stakeholder,
            $entry_parts[2]
        );
        $target_collection->add($feedback_rid);
        $this->irss->collection()->store($target_collection);
    }
}
