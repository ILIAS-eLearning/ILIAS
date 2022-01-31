<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class for mime mail registration notifications
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilRegistrationMimeMailNotification extends ilMimeMailNotification
{
    public const TYPE_NOTIFICATION_ACTIVATION = 32;

    public function send() : void
    {
        switch ($this->getType()) {
            case self::TYPE_NOTIFICATION_ACTIVATION:

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
                        ilDatePresentation::secondsToString($additional_information['hash_lifetime'], false,
                            $this->getLanguage())
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguage()->txt('reg_mail_body_3_confirmation'));
                    $this->appendBody(ilMail::_getInstallationSignature());

                    $this->sendMimeMail($this->getCurrentRecipient());
                }
                break;
        }
    }
}
