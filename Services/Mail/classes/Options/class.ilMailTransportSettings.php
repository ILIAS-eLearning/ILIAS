<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Niels Theen <ntheen@databay.de>
 */
class ilMailTransportSettings
{
    private ilMailOptions $mailOptions;

    public function __construct(ilMailOptions $mailOptions)
    {
        $this->mailOptions = $mailOptions;
    }

    public function adjust(string $firstMail, string $secondMail) : void
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
            return;
        }
    }
}
