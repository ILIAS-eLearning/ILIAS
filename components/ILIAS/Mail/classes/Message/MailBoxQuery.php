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

namespace ILIAS\Mail\Message;

use ilDBInterface;
use ilMail;
use ilDBConstants;
use DateTimeImmutable;
use DateTimeZone;
use ilTimeZone;
use ILIAS\Data\Order;
use ilUserSearchOptions;

/**
 * Database query for mails of a user
 */
class MailBoxQuery
{
    private const DEFAULT_ORDER_COLUMN = MailBoxOrderColumn::SEND_TIME;
    private const DEFAULT_ORDER_DIRECTION = Order::ASC;

    private ilDBInterface $db;
    private ?int $folder_id = null;
    private ?string $sender = null;
    private ?string $recipients = null;
    private ?string $subject = null;
    private ?string $body = null;
    private ?bool $is_unread = null;
    private ?bool $is_system = null;
    private ?bool $has_attachment = null;
    private ?DateTimeImmutable $period_start = null;
    private ?DateTimeImmutable $period_end = null;
    private ?array $filtered_ids = null;
    private int $limit = 999999;
    private int $offset = 0;

    private MailBoxOrderColumn $order_column = self::DEFAULT_ORDER_COLUMN;
    private string $order_direction = self::DEFAULT_ORDER_DIRECTION;

    public function __construct(
        private readonly int $user_id,
    ) {
        global $DIC;
        $this->db = $DIC->database();
    }

    public function withFolderId(?int $folder_id): MailBoxQuery
    {
        $clone = clone $this;
        $clone->folder_id = $folder_id;
        return $clone;
    }

    public function withSender(?string $sender): MailBoxQuery
    {
        $clone = clone $this;
        $clone->sender = $sender;
        return $clone;
    }

    public function withRecipients(?string $recipients): MailBoxQuery
    {
        $clone = clone $this;
        $clone->recipients = $recipients;
        return $clone;
    }

    public function withSubject(?string $subject): MailBoxQuery
    {
        $clone = clone $this;
        $clone->subject = $subject;
        return $clone;
    }

    public function withBody(?string $body): MailBoxQuery
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function withIsUnread(?bool $is_unread): MailBoxQuery
    {
        $clone = clone $this;
        $clone->is_unread = $is_unread;
        return $clone;
    }

    public function withIsSystem(?bool $is_system): MailBoxQuery
    {
        $clone = clone $this;
        $clone->is_system = $is_system;
        return $clone;
    }

    public function withHasAttachment(?bool $has_attachment): MailBoxQuery
    {
        $clone = clone $this;
        $clone->has_attachment = $has_attachment;
        return $clone;
    }

    public function withPeriodStart(?DateTimeImmutable $period_start): MailBoxQuery
    {
        $clone = clone $this;
        $clone->period_start = $period_start;
        return $clone;
    }

    public function withPeriodEnd(?DateTimeImmutable $period_end): MailBoxQuery
    {
        $clone = clone $this;
        $clone->period_end = $period_end;
        return $clone;
    }

    /**
     * @param int[]|null $filtered_ids
     */
    public function withFilteredIds(?array $filtered_ids): MailBoxQuery
    {
        $clone = clone $this;
        $clone->filtered_ids = $filtered_ids;
        return $clone;
    }

    public function withLimit(int $limit): MailBoxQuery
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function withOffset(int $offset): MailBoxQuery
    {
        $clone = clone $this;
        $clone->offset = $offset;
        return $clone;
    }

    public function withOrderColumn(?MailBoxOrderColumn $order_column): MailBoxQuery
    {
        $clone = clone $this;
        if (isset($order_column)) {
            $clone->order_column = $order_column;
        } else {
            $clone->order_column = self::DEFAULT_ORDER_COLUMN;
        }
        return $clone;
    }

    public function withOrderDirection(?string $order_direction): MailBoxQuery
    {
        $clone = clone $this;
        if (in_array($order_direction, [Order::ASC, Order::DESC])) {
            $clone->order_direction = $order_direction;
        } else {
            $clone->order_direction = self::DEFAULT_ORDER_DIRECTION;
        }
        return $clone;
    }


    /**
     * Count the number of unread mails with applied filter
     */
    public function countUnread(): int
    {
        return $this->withIsUnread(true)->count();
    }

    /**
     * Count the number of all mails with applied filter
     */
    public function count(): int
    {
        if ($this->filtered_ids === []) {
            return 0;
        }

        $query = 'SELECT COUNT(m.mail_id) cnt '
            . $this->getFrom()
            . $this->getWhere();

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['cnt'];
        }
        return 0;
    }

    /**
     * Get a list of mail ids
     * @return int[]
     */
    public function queryMailIds(): array
    {
        if ($this->filtered_ids === []) {
            return [];
        }

        $query = 'SELECT m.mail_id '
            . $this->getFrom()
            . $this->getWhere();

        $ids = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ids[] = (int) $row['mail_id'];
        }
        return $ids;
    }

    /**
     * Query for mail data with applied filter
     * @param bool $short get only data that is needed for a listing
     * @return MailRecordData[]
     */
    public function query($short): array
    {
        if ($this->filtered_ids === []) {
            return [];
        }

        if ($short) {
            $query = 'SELECT m.mail_id, m.user_id, m.folder_id, m.sender_id, m.send_time, '
                . 'm.m_status, m.m_subject, m.import_name, m.rcp_to, m.attachments'
                . $this->getFrom()
                . $this->getWhere();
        } else {
            $query = 'SELECT m.*'
                . $this->getFrom()
                . $this->getWhere();
        }

        if ($this->order_column === MailBoxOrderColumn::FROM) {
            $query .= ' ORDER BY '
                    . ' u.firstname ' . $this->order_direction . ', '
                    . ' u.lastname ' . $this->order_direction . ', '
                    . ' u.login ' . $this->order_direction . ', '
                    . ' m.import_name ' . $this->order_direction;
        } else {
            $query .= ' ORDER BY ' . $this->order_column->value . ' ' . $this->order_direction;
        }

        $this->db->setLimit($this->limit, $this->offset);
        $res = $this->db->query($query);

        $set = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $set[] = new MailRecordData(
                isset($row['mail_id']) ? (int) $row['mail_id'] : 0,
                isset($row['user_id']) ? (int) $row['user_id'] : 0,
                isset($row['folder_id']) ? (int) $row['folder_id'] : 0,
                isset($row['sender_id']) ? (int) $row['sender_id'] : null,
                isset($row['send_time']) ? new DateTimeImmutable($row['send_time']) : null,
                isset($row['m_status']) ? (string) $row['m_status'] : null,
                isset($row['m_subject']) ? (string) $row['m_subject'] : null,
                isset($row['import_name']) ? (string) $row['import_name'] : null,
                isset($row['use_placeholders']) ? (bool) $row['use_placeholders'] : false,
                isset($row['m_message']) ? (string) $row['m_message'] : null,
                isset($row['rcp_to']) ? (string) $row['rcp_to'] : null,
                isset($row['rcp_cc']) ? (string) $row['rcp_cc'] : null,
                isset($row['rcp_bcc']) ? (string) $row['rcp_bcc'] : null,
                isset($row['attachments']) ? (array) unserialize(
                    stripslashes($row['attachments']),
                    ['allowed_classes' => false]
                ) : [],
                isset($row['tpl_ctx_id']) ? (string) $row['tpl_ctx_id'] : null,
                isset($row['tpl_ctx_params']) ? (string) $row['tpl_ctx_params'] : null
            );
        }
        return $set;
    }

    private function getFrom()
    {
        return " FROM mail m
            LEFT JOIN usr_data u ON u.usr_id = m.sender_id
            LEFT JOIN usr_pref p ON p.usr_id = m.sender_id AND p.keyword = 'public_profile'";
    }

    private function getWhere(): string
    {
        $parts = [];

        // minimum condition: only mailbox of the given user
        $parts[] = 'm.user_id = ' . $this->db->quote($this->user_id, ilDBConstants::T_INTEGER);

        // sender conditions have to respect searchability and visibility of profile fields
        $sender_conditions = [];
        if (!empty($this->sender)) {
            $sender_conditions[] = $this->db->like('u.login', ilDBConstants::T_TEXT, '%%' . $this->sender . '%%');

            if (ilUserSearchOptions::_isEnabled('firstname')) {
                $sender_conditions[] = '(' .
                    $this->db->like('u.firstname', ilDBConstants::T_TEXT, '%%' . $this->sender . '%%')
                    . " AND p.value = 'y')";
            }

            if (ilUserSearchOptions::_isEnabled('lastname')) {
                $sender_conditions[] = '(' .
                    $this->db->like('u.lastname', ilDBConstants::T_TEXT, '%%' . $this->sender . '%%')
                    . " AND p.value = 'y')";
            }
        }
        if (!empty($sender_conditions)) {
            $parts[] = '(' . implode(' OR ', $sender_conditions) . ')';
        }

        // other text conditions
        $text_conditions = [
            [$this->recipients, 'CONCAT(CONCAT(m.rcp_to, m.rcp_cc), m.rcp_bcc)'],
            [$this->subject, 'm.m_subject'],
            [$this->body, 'm.m_message'],
        ];

        foreach ($text_conditions as $cond) {
            if (!empty($cond[0])) {
                $parts[] = $this->db->like(
                    $cond[1],
                    ilDBConstants::T_TEXT,
                    '%%' . $cond[0] . '%%',
                    false
                );
            }
        }

        if (isset($this->folder_id)) {
            $parts[] = 'm.folder_id = ' . $this->db->quote($this->folder_id, ilDBConstants::T_INTEGER);
        }

        if ($this->is_unread === true) {
            $parts[] = 'm.m_status = ' . $this->db->quote('unread', 'text');
        } elseif ($this->is_unread === false) {
            $parts[] = 'm.m_status != ' . $this->db->quote('unread', 'text');
        }

        if ($this->is_system === true) {
            $parts[] = 'm.sender_id = ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        } elseif ($this->is_system === false) {
            $parts[] = 'm.sender_id != ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        }

        if ($this->has_attachment === true) {
            $parts[] = '(m.attachments != ' . $this->db->quote(serialize(null), ilDBConstants::T_TEXT)
                            . ' AND m.attachments != ' . $this->db->quote(serialize([]), ilDBConstants::T_TEXT) . ')';
        } elseif ($this->has_attachment === false) {
            $parts[] = '(m.attachments = ' . $this->db->quote(serialize(null), ilDBConstants::T_TEXT)
                            . '  OR m.attachments = ' . $this->db->quote(serialize([]), ilDBConstants::T_TEXT) . ')';
        }

        if (!empty($this->period_start)) {
            $parts[] = 'm.send_time >= ' . $this->db->quote(
                // convert to server time zone (set by ilias initialisation)
                $this->period_start->setTimezone(new DateTimeZone(date_default_timezone_get()))
                                   ->format('Y-m-d H:i:s'),
                ilDBConstants::T_TIMESTAMP
            );
        }
        if (!empty($this->period_end)) {
            $parts[] = 'm.send_time <= ' . $this->db->quote(
                // convert to server time zone (set by ilias initialisation)
                $this->period_end->setTimezone(new DateTimeZone(date_default_timezone_get()))
                                 ->format('Y-m-d H:i:s'),
                ilDBConstants::T_TIMESTAMP
            );
        }

        if (!empty($this->filtered_ids)) {
            $parts[] = $this->db->in(
                'm.mail_id',
                $this->filtered_ids,
                false,
                ilDBConstants::T_INTEGER
            ) . ' ';
        }

        if ($parts !== []) {
            return ' WHERE ' . implode(' AND ', $parts);
        }
        return '';
    }
}
