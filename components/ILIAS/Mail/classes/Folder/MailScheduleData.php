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

use MailDeliveryData;
use DateTimeImmutable;

class MailScheduleData extends MailDeliveryData
{
    public function __construct(
        public readonly string $to,
        public readonly string $cc,
        public readonly string $bcc,
        public readonly string $subject,
        public readonly string $message,
        public readonly array $attachments,
        public readonly bool $use_placeholder,
        public readonly ?int $internal_mail_id = null,
        private readonly DateTimeImmutable $schedule_datetime
    ) {
        parent::__construct($to, $cc, $bcc, $subject, $message, $attachments, $use_placeholder, $internal_mail_id);
    }

    public function getScheduleDatetime(): DateTimeImmutable
    {
        return $this->schedule_datetime;
    }
}
