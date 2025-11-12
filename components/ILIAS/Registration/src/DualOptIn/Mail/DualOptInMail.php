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

namespace ILIAS\Registration\DualOptIn\Mail;

use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;

class DualOptInMail extends \ilMimeMailNotification
{
    public function __construct(
        private readonly \ilObjUser $user,
        private readonly PendingRegistration $pending_reg,
        private readonly int $hash_lifetime_sec
    ) {
        parent::__construct();
    }

    public function send(): void
    {
        $this->getLanguage()->loadLanguageModule('registration');

        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (\ilMailException) {
                continue;
            }

            $this->initMimeMail();
            $this->setSubject($this->getLanguage()->txt('reg_mail_subject_confirmation'));
            $this->setBody(
                $this->getLanguage()->txt('reg_mail_body_salutation')
                . ' '
                . $this->user->getFullname()
                . ','
            );
            $this->appendBody("\n\n");
            $this->appendBody($this->getLanguage()->txt('reg_mail_body_activation'));
            $this->appendBody("\n");
            $this->appendBody(
                \ilUtil::_getHttpPath()
                . '/confirmReg.php?client_id='
                . CLIENT_ID
                . '&rh='
                . $this->pending_reg->hash()->toString()
            );
            $this->appendBody("\n\n");
            $this->appendBody(
                \sprintf(
                    $this->getLanguage()->txt('reg_mail_body_2_confirmation'),
                    \ilDatePresentation::secondsToString(
                        $this->hash_lifetime_sec,
                        false,
                        $this->getLanguage()
                    )
                )
            );
            $this->appendBody("\n\n");
            $this->appendBody($this->getLanguage()->txt('reg_mail_body_3_confirmation'));
            $this->appendBody(\ilMail::_getInstallationSignature());

            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }
}
