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

    public function deliverFile(string $rid): void
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            $this->irss->consume()->download($id)->run();
        }
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

}
