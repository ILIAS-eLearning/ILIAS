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

namespace ILIAS\Test\ExportImport;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class DBRepository
{
    public const TST_EXPORT_TABLE = 'tst_exports';

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function store(
        int $object_id,
        Types $type,
        ResourceIdentification $rid
    ): void {
        $this->db->insert(
            self::TST_EXPORT_TABLE,
            [
                'object_id' => [
                    \ilDBConstants::T_INTEGER,
                    $object_id
                ],
                'type' => [
                    \ilDBConstants::T_TEXT,
                    $type->value
                ],
                'rid' => [
                    \ilDBConstants::T_TEXT,
                    $rid->serialize()
                ],
            ]
        );
    }

    public function delete(
        ResourceIdentification $rid
    ): void {
        $this->db->manipulateF(
            'DELETE FROM ' . self::TST_EXPORT_TABLE . ' WHERE rid = %s',
            [\ilDBConstants::T_TEXT],
            [$rid->serialize()]
        );
    }

    public function getFor(
        int $object_id
    ): array {
        return $this->db->fetchAll(
            $this->db->queryF(
                'SELECT * FROM ' . self::TST_EXPORT_TABLE . ' WHERE object_id = %s',
                [\ilDBConstants::T_INTEGER],
                [$object_id]
            )
        );
    }
}
