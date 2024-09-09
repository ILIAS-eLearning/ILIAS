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

use DateTimeImmutable;

class MailRecordData
{
    public const STATUS_READ = 'read';
    public const STATUS_UNREAD = 'unread';

    public function __construct(
        private readonly int $mail_id,
        private readonly int $user_id,
        private readonly int $folder_id,
        private readonly ?int $sender_id = null,
        private readonly ?DateTimeImmutable $send_time = null,
        private readonly ?string $status = null,
        private readonly ?string $subject = null,
        private readonly ?string $import_name = null,
        private readonly ?bool $use_placeholders = false,
        private readonly ?string $message = null,
        private readonly ?string $rcp_to = null,
        private readonly ?string $rcp_cc = null,
        private readonly ?string $rcp_bc = null,
        private readonly ?array $attachments = [],
        private readonly ?string $tpl_ctx_id = null,
        private readonly ?string $tpl_ctx_params = null
    ) {
    }

    public function getMailId(): int
    {
        return $this->mail_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getFolderId(): int
    {
        return $this->folder_id;
    }

    public function getSenderId(): ?int
    {
        return $this->sender_id;
    }

    public function getSendTime(): ?DateTimeImmutable
    {
        return $this->send_time;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getImportName(): ?string
    {
        return $this->import_name;
    }

    public function getUsePlaceholders(): ?bool
    {
        return $this->use_placeholders;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getRcpTo(): ?string
    {
        return $this->rcp_to;
    }

    public function getRcpCc(): ?string
    {
        return $this->rcp_cc;
    }

    public function getRcpBc(): ?string
    {
        return $this->rcp_bc;
    }

    /**
     * @return string[]|null
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function getTplCtxId(): ?string
    {
        return $this->tpl_ctx_id;
    }

    public function getTplCtxParams(): ?string
    {
        return $this->tpl_ctx_params;
    }

    public function isRead(): bool
    {
        return $this->status === self::STATUS_READ;
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function hasPersonalSender(): bool
    {
        return isset($this->sender_id) && $this->sender_id !== ANONYMOUS_USER_ID;
    }
}
