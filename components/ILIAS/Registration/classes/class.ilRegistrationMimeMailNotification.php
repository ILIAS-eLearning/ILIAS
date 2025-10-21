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

use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;

/**
 * Class for mime mail registration notifications
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilRegistrationMimeMailNotification extends ilMimeMailNotification
{
    public const int TYPE_NOTIFICATION_ACTIVATION = 32;

    private readonly ilObjUser $user;
    private readonly PendingRegistration $pending_reg;
    private readonly int $hash_lifetime_sec;


    public function __construct(ilObjUser $user, PendingRegistration $pending_reg, int $hash_lifetime_sec)
    {
        parent::__construct();

        $this->user = $user;
        $this->pending_reg = $pending_reg;
        $this->hash_lifetime_sec = $hash_lifetime_sec;
    }

    public function send(): void
    {
        if ($this->getType() !== self::TYPE_NOTIFICATION_ACTIVATION) {
            return;
        }

        $this->getLanguage()->loadLanguageModule("registration");

        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException $e) {
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
                ILIAS_HTTP_PATH
                . '/confirmReg.php?client_id='
                . CLIENT_ID
                . '&rh='
                . $this->pending_reg->getHashValue()
            );
            $this->appendBody("\n\n");
            $this->appendBody(sprintf(
                $this->getLanguage()->txt('reg_mail_body_2_confirmation'),
                ilDatePresentation::secondsToString(
                    $this->hash_lifetime_sec,
                    false,
                    $this->getLanguage()
                )
            ));
            $this->appendBody("\n\n");
            $this->appendBody($this->getLanguage()->txt('reg_mail_body_3_confirmation'));
            $this->appendBody(ilMail::_getInstallationSignature());

            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }
}
