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

namespace ILIAS\Init\PasswordAssitance\Repository;

use ILIAS\Init\PasswordAssitance\PasswordAssistanceRepository as RepositoryInterface;
use ILIAS\Init\PasswordAssitance\ValueObject\PasswordAssistanceHash;
use ILIAS\Init\PasswordAssitance\Entity\PasswordAssistanceSession;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\ObjectId;
use ILIAS\Data\Clock\ClockInterface;

class PasswordAssistanceDbRepository implements RepositoryInterface
{
    private const DEFAULT_LIFETIME_IN_SECONDS = 3600;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly ClockInterface $clock
    ) {
    }

    public function generateHash(): PasswordAssistanceHash
    {
        do {
            $hash = bin2hex(\ilPasswordUtils::getBytes(32));

            $query = 'SELECT EXISTS(SELECT 1 FROM usr_pwassist WHERE pwassist_id = %s) AS hit';

            $exists = (
                (int) ($this->db->fetchAssoc(
                    $this->db->queryF(
                        $query,
                        [\ilDBConstants::T_TEXT],
                        [$hash]
                    )
                )['hit'] ?? 0) === 1
            );
        } while ($exists);

        return new PasswordAssistanceHash($hash);
    }

    public function getSessionByUsrId(ObjectId $usr_id): Result
    {
        $query = 'SELECT * FROM usr_pwassist WHERE user_id = ' . $this->db->quote(
            $usr_id->toInt(),
            \ilDBConstants::T_INTEGER
        );
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return new Error(sprintf('No session found for usr_id %s', $usr_id->toInt()));
        }

        return new Result\Ok(
            new PasswordAssistanceSession(
                new PasswordAssistanceHash($row['pwassist_id']),
                $usr_id,
                new \DateTimeImmutable('@' . (int) $row['ctime']),
                new \DateTimeImmutable('@' . (int) $row['expires']),
            )
        );
    }

    public function getSessionByHash(PasswordAssistanceHash $hash): Result
    {
        $query = 'SELECT * FROM usr_pwassist WHERE pwassist_id = ' . $this->db->quote(
            $hash->value(),
            \ilDBConstants::T_TEXT
        );
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return new Error(sprintf('No session found for hash %s', $hash->value()));
        }

        return new Result\Ok(
            new PasswordAssistanceSession(
                $hash,
                new ObjectId((int) $row['user_id']),
                new \DateTimeImmutable('@' . (int) $row['ctime']),
                new \DateTimeImmutable('@' . (int) $row['expires']),
            )
        );
    }

    public function createSession(PasswordAssistanceHash $hash, ObjectId $usr_id): PasswordAssistanceSession
    {
        $query = 'DELETE FROM usr_pwassist ' .
            'WHERE pwassist_id = ' . $this->db->quote($hash->value(), \ilDBConstants::T_TEXT) . ' ' .
            'OR user_id = ' . $this->db->quote($usr_id->toInt(), \ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);

        $session = (new PasswordAssistanceSession($hash, $usr_id))
            ->withCreationDateTime(
                $this->clock->now()
            )->withExpirationDateTime(
                $this->clock->now()->add(new \DateInterval('PT' . self::DEFAULT_LIFETIME_IN_SECONDS . 'S'))
            );

        $this->db->manipulateF(
            'INSERT INTO usr_pwassist (pwassist_id, expires, user_id,  ctime)  VALUES (%s, %s, %s, %s)',
            [
                \ilDBConstants::T_TEXT,
                \ilDBConstants::T_INTEGER,
                \ilDBConstants::T_INTEGER,
                \ilDBConstants::T_INTEGER
            ],
            [
                $session->hash()->value(),
                $session->expirationDateTime()->getTimestamp(),
                $session->usrId()->toInt(),
                $session->creationDateTime()->getTimestamp()
            ]
        );

        return $session;
    }

    public function deleteSession(PasswordAssistanceSession $session): void
    {
        $query = 'DELETE FROM usr_pwassist WHERE pwassist_id = ' . $this->db->quote(
            $session->hash()->value(),
            \ilDBConstants::T_TEXT
        );
        $this->db->manipulate($query);
    }
}
