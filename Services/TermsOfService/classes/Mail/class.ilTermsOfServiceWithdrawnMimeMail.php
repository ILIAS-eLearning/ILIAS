<?php declare(strict_types=1);

/**
 * Class ilTermsOfServiceWithdrawnMimeMail
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceWithdrawnMimeMail extends ilMimeMailNotification
{
    public function send() : void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $data = $this->getAdditionalInformation();
        /** @var ilObjUser $subjectUser */
        $subjectUser = $data['user'];

        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException $e) {
                continue;
            }

            if (!($subjectUser instanceof ilObjUser) || !$this->getCurrentRecipient()) {
                continue;
            }

            $this->initMimeMail();
            $this->initLanguageByIso2Code();

            $this->setSubject($this->getLanguage()->txt('withdrawal_mail_subject'));

            $body = str_ireplace("[BR]", "\n", sprintf(
                $this->getLanguage()->txt('withdrawal_mail_text'),
                $subjectUser->getFullname(),
                $subjectUser->getLogin(),
                $subjectUser->getExternalAccount()
            ));
            $this->appendBody($body);
            $this->appendBody(ilMail::_getInstallationSignature());

            $this->sendMimeMail($this->getCurrentRecipient());
        }

        ilDatePresentation::setLanguage($lng);
    }
}