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

namespace ILIAS\Services\Mail\AutoResponder;

use ilDBInterface;
use DateTimeImmutable;
use DateTimeZone;

class AutoResponderDatabaseRepository implements ilAutoResponderRepository
{
    public const TABLE_NAME = 'auto_responder';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function findBySenderId(int $sender_id) : array
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE sender_id = " . $this->db->quote($sender_id, 'integer');

        $result = $this->db->query($query);

        $auto_responder_results = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $auto_responder_results[] = new AutoResponder(
                (int) $row['sender_id'],
                (int) $row['receiver_id'],
                new DateTimeImmutable($row['send_time'], new DateTimeZone(date_default_timezone_get()))
            );
        }
        return $auto_responder_results;
    }

    public function findByReceiverId(int $receiver_id) : array
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE receiver_id = " . $this->db->quote(
            $receiver_id,
            'integer'
        );

        $result = $this->db->query($query);

        $auto_responder_results = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $auto_responder_results[] = new AutoResponder(
                (int) $row['sender_id'],
                (int) $row['receiver_id'],
                new DateTimeImmutable($row['send_time'], new DateTimeZone(date_default_timezone_get()))
            );
        }
        return $auto_responder_results;
    }

    public function read(AutoResponder $auto_responder) : AutoResponder
    {
        return $this->findBySenderIdAndReceiverId($auto_responder->getSenderId(), $auto_responder->getReceiverId());
    }

    public function findBySenderIdAndReceiverId(int $sender_id, int $receiver_id) : ?AutoResponder
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE sender_id = " . $this->db->quote(
            $sender_id,
            'integer'
        ) . " AND receiver_id = " . $this->db->quote($receiver_id, 'integer');
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        return $row ? new AutoResponder(
            (int) $row['sender_id'],
            (int) $row['receiver_id'],
            new DateTimeImmutable($row['send_time'], new DateTimeZone(date_default_timezone_get()))
        ) : null;
    }

    public function store(AutoResponder $auto_responder) : void
    {
        $timestamp_send_time = $auto_responder->getSendTime()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $this->db->replace(
            self::TABLE_NAME,
            [
                'sender_id' => ['integer', $auto_responder->getSenderId()],
                'receiver_id' => ['integer', $auto_responder->getReceiverId()]
            ],
            [
                'send_time' => ['timestamp', $timestamp_send_time]
            ]
        );
    }

    public function delete(AutoResponder $auto_responder) : void
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE sender_id = ' . $this->db->quote(
            $auto_responder->getSenderId(),
            'integer'
        ) . ' AND receiver_id = ' . $this->db->quote($auto_responder->getReceiverId(), 'integer'));
    }

    public function deleteBySenderId(int $sender_id) : void
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE sender_id = ' . $this->db->quote(
            $sender_id,
            'integer'
        ));
    }
}
