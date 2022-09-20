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

namespace ILIAS\Mail\Cron\ExpiredOrOrphanedMails;

class MailDto
{
    public function __construct(private int $mail_id, private ?string $mail_subject)
    {
    }

    public function getMailId(): int
    {
        return $this->mail_id;
    }

    public function getMailSubject(): ?string
    {
        return $this->mail_subject;
    }
}
