<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Niels Theen <ntheen@databay.de>
 * @version $Id$
 */
class ilMailTransportSettings
{
    /** @var ilMailOptions */
    private $mailOptions;

    /**
     * ilMailTransportSettings constructor.
     * @param ilMailOptions $mailOptions
     */
    public function __construct(ilMailOptions $mailOptions)
    {
        $this->mailOptions = $mailOptions;
    }

    /**
     * Validates the current instance settings and eventually adjusts these
     * @param string $firstMail
     * @param string $secondMail
     */
    public function adjust(string $firstMail, string $secondMail, bool $persist = true) : void
    {
        if ($this->mailOptions->getIncomingType() === ilMailOptions::INCOMING_LOCAL) {
            return;
        }

        $hasFirstEmail = strlen($firstMail) > 0;
        $hasSecondEmail = strlen($secondMail) > 0;

        if (!$hasFirstEmail && !$hasSecondEmail) {
            $this->mailOptions->setIncomingType(ilMailOptions::INCOMING_LOCAL);
            if ($persist) {
                $this->mailOptions->updateOptions();
            }
            return;
        }

        if (!$hasFirstEmail && $this->mailOptions->getEmailAddressMode() !== ilMailOptions::SECOND_EMAIL) {
            $this->mailOptions->setEmailAddressMode(ilMailOptions::SECOND_EMAIL);
            if ($persist) {
                $this->mailOptions->updateOptions();
            }
            return;
        }

        if (!$hasSecondEmail && $this->mailOptions->getEmailAddressMode() !== ilMailOptions::FIRST_EMAIL) {
            $this->mailOptions->setEmailAddressMode(ilMailOptions::FIRST_EMAIL);
            if ($persist) {
                $this->mailOptions->updateOptions();
            }
            return;
        }
    }
}
