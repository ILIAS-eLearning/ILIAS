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

namespace ILIAS\MediaObjects;

use ilDBInterface;
use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\ZIPStream;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class MediaObjectRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected IRSSWrapper $irss
    ) {
    }

    public function create(
        int $id,
        string $title,
        \ilMobStakeholder $stakeholder,
        int $from_mob_id = 0
    ) : void {
        if ($from_mob_id > 0) {
            $from_rid = $this->getRidForMobId($from_mob_id);
            $rid = $this->irss->cloneContainer($from_rid);
        } else {
            $rid = $this->irss->createContainer(
                $stakeholder,
                "mob.zip"
            );
        }
        $this->db->insert('mob_data', [
            'id' => ['integer', $id],
            'rid' => ['text', $rid]
        ]);
    }

    public function getById(int $id) : ?array
    {
        $set = $this->db->queryF(
            'SELECT * FROM mob_data WHERE id = %s',
            ['integer'],
            [$id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record) {
            return [
                'id' => (int) $record['id'],
                'rid' => (string) $record['rid']
            ];
        }

        return null;
    }

    public function delete(int $id) : void
    {
        $this->db->manipulateF(
            'DELETE FROM mob_data WHERE id = %s',
            ['integer'],
            [$id]
        );
    }

    protected function getRidForMobId(int $mob_id) : string
    {
        $set = $this->db->queryF(
            "SELECT * FROM mob_data " .
            " WHERE id = %s ",
            ["integer"],
            [$mob_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return $rec["rid"] ?? "";
        }
        return "";
    }

    public function addFileFromLegacyUpload(int $mob_id, string $tmp_name, string $target_path = "") : void
    {
        if ($rid = $this->getRidForMobId($mob_id)) {
            if ($target_path === "") {
                $target_path = "/";
            }
            $this->irss->importFileFromLegacyUploadToContainer(
                $rid,
                $tmp_name,
                $target_path
            );
        }
    }

    public function addFileFromUpload(
        int $mob_id,
        UploadResult $result,
        string $path = "/"
    ) : void {
        if ($rid = $this->getRidForMobId($mob_id)) {
            $this->irss->importFileFromUploadResultToContainer(
                $rid,
                $result,
                $path
            );
        }
    }

    public function addFileFromLocal(int $mob_id, string $tmp_name, string $path) : void
    {
        if ($rid = $this->getRidForMobId($mob_id)) {
            $this->irss->addLocalFileToContainer(
                $rid,
                $tmp_name,
                $path
            );
        }
    }

    public function addLocalDirectory(int $mob_id, string $dir) : void
    {
        if ($rid = $this->getRidForMobId($mob_id)) {
            $this->irss->addDirectoryToContainer(
                $rid,
                $dir
            );
        }
    }

    public function getLocalSrc(int $mob_id, string $location) : string
    {
        return $this->irss->getContainerUri($this->getRidForMobId($mob_id), $location);
    }

    public function hasLocalFile(int $mob_id, string $location) : bool
    {
        return $this->irss->hasContainerEntry($this->getRidForMobId($mob_id), $location);
    }

    public function getLocationStream(
        int $mob_id,
        string $location
    ) : ZIPStream {
        return $this->irss->getStreamOfContainerEntry(
            $this->getRidForMobId($mob_id),
            $location
        );
    }

    public function getInfoOfEntry(
        int $mob_id,
        string $path
    )
    {
        return $this->irss->getContainerEntryInfo(
            $this->getRidForMobId($mob_id),
            $path
        );
    }

    public function deliverEntry(
        int $mob_id,
        string $path
    ) : void
    {
        $this->irss->deliverContainerEntry(
            $this->getRidForMobId($mob_id),
            $path
        );
    }

    public function getContainerPath(
        int $mob_id
    ) : string {
        return $this->irss->getResourcePath($this->getRidForMobId($mob_id));
    }

    public function addStream(
        int $mob_id,
        string $location,
        FileStream $stream
    ) : void {
        $this->irss->addStreamToContainer(
            $this->getRidForMobId($mob_id),
            $stream,
            $location
        );
    }

    public function getContainerResource(
        int $mob_id
    ) : ?StorableResource {
        return $this->irss->getResource($this->getRidForMobId($mob_id));
    }

    public function getContainerResourceId(
        int $mob_id
    ) : ?ResourceIdentification {
        return $this->irss->getResourceIdForIdString($this->getRidForMobId($mob_id));
    }

    public function removeLocation(
        int $mob_id,
        string $location
    ) : void {
        $this->irss->removePathFromContainer($this->getRidForMobId($mob_id), $location);
    }

    public function getFilesOfPath(
        int $mob_id,
        string $dir_path
    ) : array {
        return $this->irss->getContainerEntriesOfPath(
            $this->getRidForMobId($mob_id),
            $dir_path
        );
    }

}
