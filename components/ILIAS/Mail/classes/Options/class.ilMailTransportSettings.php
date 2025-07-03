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

class ilMailTransportSettings
{
    public function __construct(private readonly ilMailOptions $mail_options)
    {
    }

    public function adjust(string $first_mail, string $second_mail, bool $persist = true): void
    {
        if ($this->mail_options->getIncomingType() === ilMailOptions::INCOMING_LOCAL) {
            return;
        }

        $has_first_email = $first_mail !== '';
        $has_second_email = $second_mail !== '';

        if (!$has_first_email && !$has_second_email) {
            $this->mail_options->setIncomingType(ilMailOptions::INCOMING_LOCAL);
            if ($persist) {
                $this->mail_options->updateOptions();
            }
            return;
        }

        if (!$has_first_email && $this->mail_options->getEmailAddressMode() !== ilMailOptions::SECOND_EMAIL) {
            $this->mail_options->setEmailAddressmode(ilMailOptions::SECOND_EMAIL);
            if ($persist) {
                $this->mail_options->updateOptions();
            }
            return;
        }

        if (!$has_second_email && $this->mail_options->getEmailAddressMode() !== ilMailOptions::FIRST_EMAIL) {
            $this->mail_options->setEmailAddressmode(ilMailOptions::FIRST_EMAIL);
            if ($persist) {
                $this->mail_options->updateOptions();
            }
        }
    }
}
