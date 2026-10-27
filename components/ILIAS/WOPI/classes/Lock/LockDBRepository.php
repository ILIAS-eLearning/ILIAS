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

namespace ILIAS\WOPI\Lock;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LockDBRepository implements LockRepository
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'wopi_lock';

    public function __construct(
        private \ilDBInterface $db
    ) {
    }

    public function find(string $resource_id): ?Lock
    {
        $result = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE resource_id = %s',
            ['text'],
            [$resource_id]
        );
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return null;
        }

        return new Lock(
            (string) $row['lock_value'],
            (int) $row['expires'],
            (int) $row['user_id']
        );
    }

    public function store(string $resource_id, Lock $lock): void
    {
        $this->db->replace(
            self::TABLE_NAME,
            [
                'resource_id' => ['text', $resource_id],
            ],
            [
                'lock_value' => ['text', $lock->value],
                'expires' => ['integer', $lock->expires],
                'user_id' => ['integer', $lock->user_id],
            ]
        );
    }

    public function delete(string $resource_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE resource_id = %s',
            ['text'],
            [$resource_id]
        );
    }
}
