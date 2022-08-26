<?php

declare(strict_types=1);

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

/**
 * Class ilTermsOfServiceWithdrawnMimeMail
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceWithdrawnMimeMail extends ilMimeMailNotification
{
    public function send(): void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $data = $this->getAdditionalInformation();
        /** @var ilObjUser $subjectUser */
        $subjectUser = $data['user'];

        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException) {
                continue;
            }

            if (!($subjectUser instanceof ilObjUser) || !$this->getCurrentRecipient()) {
                continue;
            }

            $this->initMimeMail();
            $this->initLanguageByIso2Code();

            $this->setSubject($this->getLanguage()->txt('withdrawal_mail_subject'));

            $body = str_ireplace('[BR]', "\n", sprintf(
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
