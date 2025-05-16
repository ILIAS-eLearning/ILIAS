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

class ilMailValueObject
{
    /** @var list<string> */
    private readonly array $attachments;

    /**
     * @param list<string> $attachments
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $from,
        private readonly string $recipients,
        private readonly string $recipients_cc,
        private readonly string $recipients_bcc,
        private readonly string $subject,
        private readonly string $body,
        array $attachments,
        private readonly bool $use_placeholders = false,
        private readonly bool $save_in_sent_box = false
    ) {
        $this->attachments = array_filter(array_map('trim', $attachments));
        if (ilStr::strLen($this->subject) > 255) {
            throw new InvalidArgumentException('Subject must not be longer than 255 characters');
        }
    }

    public function getRecipients(): string
    {
        return $this->recipients;
    }

    public function getRecipientsCC(): string
    {
        return $this->recipients_cc;
    }

    public function getRecipientsBCC(): string
    {
        return $this->recipients_bcc;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return list<string>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function isUsingPlaceholders(): bool
    {
        return $this->use_placeholders;
    }

    public function shouldSaveInSentBox(): bool
    {
        return $this->save_in_sent_box;
    }

    public function getFrom(): string
    {
        return $this->from;
    }
}
