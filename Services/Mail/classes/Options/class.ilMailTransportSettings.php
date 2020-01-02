<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

 /**
 * @author Niels Theen <ntheen@databay.de>
 * @version $Id$
 */
class ilMailTransportSettings
{
    private $mailOptions;

    public function __construct(ilMailOptions $mailOptions)
    {
        $this->mailOptions = $mailOptions;
    }

    /**
     * Validates the current instance settings and eventually adjusts these
     * @param string $firstMail
     * @param string $secondMail
     * @return int|string|void
     */
    public function adjust($firstMail, $secondMail)
    {
        if ($this->mailOptions->getIncomingType() === ilMailOptions::INCOMING_LOCAL) {
            return;
        }

        $hasFirstEmail  = strlen($firstMail);
        $hasSecondEmail = strlen($secondMail);

        if (!$hasFirstEmail && !$hasSecondEmail) {
            $this->mailOptions->setIncomingType(ilMailOptions::INCOMING_LOCAL);
            return $this->mailOptions->updateOptions();
        }

        if (!$hasFirstEmail && $this->mailOptions->getMailAddressOption() !== ilMailOptions::SECOND_EMAIL) {
            $this->mailOptions->setMailAddressOption(ilMailOptions::SECOND_EMAIL);
            return $this->mailOptions->updateOptions();
        }

        if (!$hasSecondEmail && $this->mailOptions->getMailAddressOption() !== ilMailOptions::FIRST_EMAIL) {
            $this->mailOptions->setMailAddressOption(ilMailOptions::FIRST_EMAIL);
            return $this->mailOptions->updateOptions();
        }
    }
}
