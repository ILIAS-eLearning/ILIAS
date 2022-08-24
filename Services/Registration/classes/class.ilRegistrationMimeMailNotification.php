<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class for mime mail registration notifications
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilRegistrationMimeMailNotification extends ilMimeMailNotification
{
    public const TYPE_NOTIFICATION_ACTIVATION = 32;

    public function send(): void
    {
        if ($this->getType() === self::TYPE_NOTIFICATION_ACTIVATION) {
            $additional_information = $this->getAdditionalInformation();
            /**
             * @var $user ilObjUser
             */
            $user = $additional_information['usr'];
            $this->getLanguage()->loadLanguageModule("registration");

            foreach ($this->getRecipients() as $rcp) {
                try {
                    $this->handleCurrentRecipient($rcp);
                } catch (ilMailException $e) {
                    continue;
                }

                $this->initMimeMail();
                $this->setSubject($this->getLanguage()->txt('reg_mail_subject_confirmation'));
                $this->setBody($this->getLanguage()->txt('reg_mail_body_salutation') . ' ' . $user->getFullname() . ',');
                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguage()->txt('reg_mail_body_activation'));
                $this->appendBody("\n");
                $this->appendBody(ILIAS_HTTP_PATH . '/confirmReg.php?client_id=' . CLIENT_ID . '&rh=' . ilObjUser::_generateRegistrationHash($user->getId()));
                $this->appendBody("\n\n");
                $this->appendBody(sprintf(
                    $this->getLanguage()->txt('reg_mail_body_2_confirmation'),
                    ilDatePresentation::secondsToString(
                        $additional_information['hash_lifetime'],
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
}
