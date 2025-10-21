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

namespace ILIAS\Registration\DualOptIn\Repository;

use DateTimeImmutable;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationId;

readonly class PendingRegistrationDatabaseRepository implements PendingRegistrationRepository
{
    public function __construct(protected ilDBInterface $db)
    {
    }

    public function findNewHash(): PendingRegistrationHash
    {
        do {
            $unique_id = uniqid((string) mt_rand(), true);
            $hash = substr(md5($unique_id), 0, 16);

            $res = $this->db->queryf(
                'SELECT COUNT(usr_id) cnt FROM reg_dual_opt_in WHERE reg_hash = %s',
                [ilDBConstants::T_TEXT],
                [$hash]
            );
            $row = $this->db->fetchObject($res);
            if ($row->cnt > 0) {
                continue;
            }
            break;
        } while (true);

        return new PendingRegistrationHash($hash);
    }

    public function store(PendingRegistration $reg): void
    {
        $this->db->manipulateF(
            'REPLACE INTO reg_dual_opt_in (id, usr_id, reg_hash, creation_date) VALUES (%s, %s, %s, %s)',
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            [$reg->getId(), $reg->getUserId(), $reg->getHashValue(), $reg->getCreateDate()->getTimestamp()]
        );
    }

    public function findByHashValue(string $hash_value): ?PendingRegistration
    {
        $res = $this->db->queryf(
            'SELECT id, usr_id, reg_hash, creation_date FROM reg_dual_opt_in WHERE reg_hash = %s',
            [ilDBConstants::T_TEXT],
            [$hash_value]
        );

        $row = $this->db->fetchAssoc($res);
        if (!$row) {
            return null;
        }

        return $this->rebuildObjFromRow($row);
    }

    public function deleteById(string $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM reg_dual_opt_in WHERE id = %s',
            [ilDBConstants::T_TEXT],
            [$id]
        );
    }

    public function deleteByUserId(int $usr_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM reg_dual_opt_in WHERE usr_id = %s',
            [ilDBConstants::T_INTEGER],
            [$usr_id]
        );
    }

    /**
     * @return list<PendingRegistration>
     */
    public function deleteExpired(int $cutoff_ts, ?int $prioritize_usr_id = null): array
    {
        $except_anon_and_sys = $this->db->in(
            'h.usr_id',
            [ANONYMOUS_USER_ID, SYSTEM_USER_ID],
            true,
            ilDBConstants::T_INTEGER
        );

        $query = '
            SELECT h.*
              FROM reg_dual_opt_in h
              JOIN usr_data u ON u.usr_id = h.usr_id
             WHERE u.active = 0
               AND h.creation_date < %s
               AND ' . $except_anon_and_sys . '
             ORDER BY (CASE WHEN h.usr_id = %s THEN 0 ELSE 1 END), h.creation_date
         ';

        $res = $this->db->queryF(
            $query,
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$cutoff_ts, $prioritize_usr_id ?? 0]
        );

        $expired_registrations = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $this->deleteById($row['id']);
            $expired_registrations[] = $this->rebuildObjFromRow($row);
        }

        return $expired_registrations;
    }

    /**
     * @param array{id: string, usr_id: int, reg_hash: string, creation_date: int} $row
     */
    private function rebuildObjFromRow(array $row): PendingRegistration
    {
        $id = new PendingRegistrationId($row['id']);
        $usr_id = new ObjectId($row['usr_id']);
        $reg_hash = new PendingRegistrationHash($row['reg_hash']);
        $created_at = new DateTimeImmutable('@' . $row['creation_date']);

        return new PendingRegistration($id, $usr_id, $reg_hash, $created_at);
    }
}
