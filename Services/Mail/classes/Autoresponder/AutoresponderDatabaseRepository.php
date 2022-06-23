<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Autoresponder;

use ilDBInterface;
use DateTimeImmutable;
use DateTimeZone;
use ilObjectNotFoundException;
use ilDBConstants;

final class AutoresponderDatabaseRepository implements AutoresponderRepository
{
    private const TABLE_NAME = 'mail_auto_responder';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function findBySenderId(int $sender_id): AutoresponderArrayCollection
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE sender_id = " . $this->db->quote($sender_id, ilDBConstants::T_INTEGER);

        $result = $this->db->query($query);

        $auto_responder_results = new AutoresponderArrayCollection();
        while ($row = $this->db->fetchAssoc($result)) {
            $auto_responder_results->add(new AutoresponderDto(
                (int) $row['sender_id'],
                (int) $row['receiver_id'],
                new DateTimeImmutable($row['sent_time'], new DateTimeZone('UTC'))
            ));
        }

        return $auto_responder_results;
    }

    public function findByReceiverId(int $receiver_id): AutoresponderArrayCollection
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE receiver_id = " . $this->db->quote(
            $receiver_id,
            ilDBConstants::T_INTEGER
        );

        $result = $this->db->query($query);

        $auto_responder_results = new AutoresponderArrayCollection();
        while ($row = $this->db->fetchAssoc($result)) {
            $auto_responder_results->add(new AutoresponderDto(
                (int) $row['sender_id'],
                (int) $row['receiver_id'],
                new DateTimeImmutable($row['sent_time'], new DateTimeZone('UTC'))
            ));
        }

        return $auto_responder_results;
    }

    /**
     * @throws ilObjectNotFoundException
     */
    public function findBySenderIdAndReceiverId(int $sender_id, int $receiver_id): AutoresponderDto
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE sender_id = " . $this->db->quote(
            $sender_id,
            ilDBConstants::T_INTEGER
        ) . " AND receiver_id = " . $this->db->quote($receiver_id, ilDBConstants::T_INTEGER);
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        if (!$row) {
            throw new ilObjectNotFoundException(
                "No auto responder found for sender_id: " . $sender_id . " and receiver_id: " . $receiver_id
            );
        }

        return new AutoresponderDto(
            (int) $row['sender_id'],
            (int) $row['receiver_id'],
            new DateTimeImmutable($row['sent_time'], new DateTimeZone('UTC'))
        );
    }

    public function store(AutoresponderDto $auto_responder): void
    {
        $timestamp_sent_time = $auto_responder->getSentTime()->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $this->db->replace(
            self::TABLE_NAME,
            [
                'sender_id' => [ilDBConstants::T_INTEGER, $auto_responder->getSenderId()],
                'receiver_id' => [ilDBConstants::T_INTEGER, $auto_responder->getReceiverId()]
            ],
            [
                'sent_time' => [ilDBConstants::T_TIMESTAMP, $timestamp_sent_time]
            ]
        );
    }

    public function delete(AutoresponderDto $auto_responder): void
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE sender_id = ' . $this->db->quote(
            $auto_responder->getSenderId(),
            ilDBConstants::T_INTEGER
        ) . ' AND receiver_id = ' . $this->db->quote($auto_responder->getReceiverId(), ilDBConstants::T_INTEGER));
    }

    public function deleteBySenderId(int $sender_id): void
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE sender_id = ' . $this->db->quote(
            $sender_id,
            ilDBConstants::T_INTEGER
        ));
    }

    public function exists(int $sender_id, int $receiver_id): bool
    {
        $query = "SELECT 1 existing_record FROM " . self::TABLE_NAME . " WHERE sender_id = " . $this->db->quote(
            $sender_id,
            ilDBConstants::T_INTEGER
        ) . " AND receiver_id = " . $this->db->quote($receiver_id, ilDBConstants::T_INTEGER);
        $result = $this->db->query($query);

        if ($row = $this->db->fetchAssoc($result)) {
            return (int) $row['existing_record'] === 1;
        }

        return false;
    }
}
