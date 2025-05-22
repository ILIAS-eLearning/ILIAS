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

namespace ILIAS\Mail\Folder;

use DateTimeImmutable;

/**
 * Filter data for display of mail records
 * Properties with null value will not be applied as a filter
 */
class MailFilterData
{
    public function __construct(
        private readonly ?string $sender,
        private readonly ?string $recipients,
        private readonly ?string $subject,
        private readonly ?string $body,
        private readonly ?string $attachment,
        private readonly ?DateTimeImmutable $period_start,
        private readonly ?DateTimeImmutable $period_end,
        private readonly ?bool $is_unread,
        private readonly ?bool $is_system,
        private readonly ?bool $has_attachment
    ) {
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function getRecipients(): ?string
    {
        return $this->recipients;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function getPeriodStart(): ?DateTimeImmutable
    {
        return $this->period_start;
    }

    public function getPeriodEnd(): ?DateTimeImmutable
    {
        return $this->period_end;
    }

    public function isUnread(): ?bool
    {
        return $this->is_unread;
    }

    public function isSystem(): ?bool
    {
        return $this->is_system;
    }

    public function hasAttachment(): ?bool
    {
        return $this->has_attachment;
    }
}
