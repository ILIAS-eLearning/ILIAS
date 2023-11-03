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

namespace ILIAS\Exercise\InstructionFile;

use ILIAS\Exercise\IRSS\CollectionWrapper;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;

class InstructionFileRepository
{
    protected CollectionWrapper $wrapper;
    protected CollectionWrapper $collection;
    protected \ilDBInterface $db;

    public function __construct(
        CollectionWrapper $wrapper,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->wrapper = $wrapper;
    }

    public function createCollection(int $ass_id): void
    {
        $new_id = $this->wrapper->createEmptyCollection();
        $this->db->update(
            "exc_assignment",
            [
            "if_rcid" => ["text", $new_id]
        ],
            [    // where
                "id" => ["integer", $ass_id]
            ]
        );
    }

    public function getIdStringForAssId(int $ass_id): string
    {
        $set = $this->db->queryF(
            "SELECT if_rcid FROM exc_assignment " .
            " WHERE id = %s ",
            ["integer"],
            [$ass_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["if_rcid"] ?? "");
    }

    public function hasCollection(int $ass_id): bool
    {
        $rcid = $this->getIdStringForAssId($ass_id);
        return ($rcid !== "");
    }

    public function getCollection(int $ass_id): ?ResourceCollection
    {
        $rcid = $this->getIdStringForAssId($ass_id);
        if ($rcid !== "") {
            return $this->wrapper->getCollectionForIdString($rcid);
        }
        return null;
    }

    public function importFromLegacyUpload(
        int $ass_id,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): void {
        $collection = $this->getCollection($ass_id);
        if ($collection) {
            $this->wrapper->importFilesFromLegacyUploadToCollection(
                $collection,
                $file_input,
                $stakeholder
            );
        }
    }

    public function deliverFile($ass_id, $file): void
    {
        /** @var ResourceInformation $info */
        foreach ($this->getCollectionResourcesInfo($ass_id) as $info) {
            if ($file === $info->getTitle()) {
                $this->wrapper->deliverFile($info->getRid());
            }
        }
        throw new \ilExerciseException("Resource $file not found.");
    }

    public function getCollectionResourcesInfo(
        int $ass_id
    ): \Generator {
        $collection = $this->getCollection($ass_id);
        return $this->wrapper->getCollectionResourcesInfo($collection);
    }

    public function deleteCollection(
        int $ass_id,
        ResourceStakeholder $stakeholder
    ): void {
        $rcid = $this->getIdStringForAssId($ass_id);
        if ($rcid === "") {
            return;
        }
        $this->wrapper->deleteCollectionForIdString(
            $rcid,
            $stakeholder
        );
    }

    public function clone(
        int $from_ass_id,
        int $to_ass_id
    ): void {
        $from_rcid = $this->getIdStringForAssId($from_ass_id);
        $to_rcid = $this->wrapper->clone($from_rcid);
        if ($to_rcid !== "") {
            $this->db->update(
                "exc_assignment",
                [
                "if_rcid" => ["text", $to_rcid]
            ],
                [    // where
                    "id" => ["integer", $to_ass_id]
                ]
            );
        }
    }
}
