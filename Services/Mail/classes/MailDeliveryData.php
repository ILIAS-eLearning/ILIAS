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

final class MailDeliveryData
{
    public function __construct(
        private readonly string $to,
        private readonly string $cc,
        private readonly string $bcc,
        private readonly string $subject,
        private readonly string $message,
        private readonly array $attachments,
        private readonly bool $use_placeholder,
        private ?int $internal_mail_id = null
    ) {
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getCc(): string
    {
        return $this->cc;
    }

    public function getBcc(): string
    {
        return $this->bcc;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getInternalMailId(): ?int
    {
        return $this->internal_mail_id;
    }

    public function isUsePlaceholder(): bool
    {
        return $this->use_placeholder;
    }

    public function withInternalMailId(int $internal_mail_id): MailDeliveryData
    {
        $clone = clone $this;
        $clone->internal_mail_id = $internal_mail_id;
        return $clone;
    }
}
