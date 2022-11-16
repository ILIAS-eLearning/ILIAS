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

use DateTimeImmutable;

final class AutoresponderDto
{
    /** @var int $sender_id */
    private $sender_id;
    /** @var int $receiver_id */
    private $receiver_id;
    /** @var DateTimeImmutable $sent_time */
    private $sent_time;

    public function __construct(int $sender_id, int $receiver_id, DateTimeImmutable $sent_time)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->sent_time = $sent_time;
    }

    public function getSenderId() : int
    {
        return $this->sender_id;
    }

    public function withSenderId(int $sender_id) : self
    {
        $clone = clone $this;
        $clone->sender_id = $sender_id;
        return $clone;
    }

    public function getReceiverId() : int
    {
        return $this->receiver_id;
    }

    public function withReceiverId(int $receiver_id) : self
    {
        $clone = clone $this;
        $clone->receiver_id = $receiver_id;
        return $clone;
    }

    public function getSentTime() : DateTimeImmutable
    {
        return $this->sent_time;
    }

    public function withSentTime(DateTimeImmutable $sent_time) : self
    {
        $clone = clone $this;
        $clone->sent_time = $sent_time;
        return $clone;
    }
}
