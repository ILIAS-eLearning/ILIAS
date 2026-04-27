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

use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class QstsTempAttemptRepository
{
    public const string TABLE_NAME = 'qsts_temp_attempt';

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly UuidFactory $uuid_factory
    ) {
    }

    public function get(
        int $user_id,
    ): ?Uuid {
        $value = $this->db->fetchObject(
            $this->db->queryF(
                'SELECT attempt_id from ' . self::TABLE_NAME . ' WHERE user_id = %s',
                [FieldDefinition::T_INTEGER],
                [$user_id]
            )
        );

        if ($value === null) {
            return null;
        }

        return $this->uuid_factory->fromString($value->attempt_id);
    }

    public function store(
        int $user_id,
        Uuid $attempt_id
    ): void {
        $this->db->insert(
            self::TABLE_NAME,
            [
                'user_id' => [FieldDefinition::T_INTEGER, $user_id],
                'attempt_id' => [FieldDefinition::T_TEXT, $attempt_id->toString()]
            ]
        );
    }
}
