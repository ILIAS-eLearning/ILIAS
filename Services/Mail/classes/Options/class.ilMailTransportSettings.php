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

/**
 * @author Niels Theen <ntheen@databay.de>
 */
class ilMailTransportSettings
{
    public function __construct(private readonly ilMailOptions $mailOptions)
    {
    }

    public function adjust(string $firstMail, string $secondMail): void
    {
        if ($this->mailOptions->getIncomingType() === ilMailOptions::INCOMING_LOCAL) {
            return;
        }

        $hasFirstEmail = $firstMail !== '';
        $hasSecondEmail = $secondMail !== '';

        if (!$hasFirstEmail && !$hasSecondEmail) {
            $this->mailOptions->setIncomingType(ilMailOptions::INCOMING_LOCAL);
            $this->mailOptions->updateOptions();
            return;
        }

        if (!$hasFirstEmail && $this->mailOptions->getEmailAddressMode() !== ilMailOptions::SECOND_EMAIL) {
            $this->mailOptions->setEmailAddressMode(ilMailOptions::SECOND_EMAIL);
            $this->mailOptions->updateOptions();
            return;
        }

        if (!$hasSecondEmail && $this->mailOptions->getEmailAddressMode() !== ilMailOptions::FIRST_EMAIL) {
            $this->mailOptions->setEmailAddressMode(ilMailOptions::FIRST_EMAIL);
            $this->mailOptions->updateOptions();
        }
    }
}
