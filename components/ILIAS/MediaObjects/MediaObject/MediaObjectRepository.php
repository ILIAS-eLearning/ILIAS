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
use ILIAS\Exercise\IRSS\IRSSWrapper;

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
        \ilMobStakeholder $stakeholder
    ): void {
        $rid = $this->irss->createContainer(
            $stakeholder
        );
        $this->db->insert('mob_data', [
            'id' => ['integer', $id],
            'rid' => ['text', $rid]
        ]);
    }

    public function getById(int $id): ?array
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

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM mob_data WHERE id = %s',
            ['integer'],
            [$id]
        );
    }

    protected function getRidForMobId(int $mob_id): string
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

    public function addFileFromLegacyUpload(int $mob_id, string $upload_name, string $targetpath): void
    {
        if ($rid = $this->getRidForMobId($mob_id)) {
            $this->irss->importFileFromLegacyUploadToContainer(
                $rid,
                $_FILES[$upload_name],
                "/"
            );
        }
    }

    public function getLocationSrc(int $mob_id, string $location): string
    {
        return $this->irss->getContainerSrc($this->getRidForMobId($mob_id), $location);
    }

}
