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
 * Base class for mime mail notifications
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
abstract class ilMimeMailNotification extends ilMailNotification
{
    protected ilMimeMail $mime_mail;
    protected string $current_recipient;
    protected ilMailMimeSenderFactory $senderFactory;

    public function __construct(bool $a_is_personal_workspace = false)
    {
        global $DIC;
        $this->senderFactory = $DIC->mail()->mime()->senderFactory();
        parent::__construct($a_is_personal_workspace);
    }

    public function sendMimeMail(string $a_rcp): void
    {
        $this->mime_mail->To($a_rcp);
        $this->mime_mail->Subject($this->getSubject(), true);
        $this->mime_mail->Body($this->getBody());
        $this->mime_mail->Send();
    }

    protected function initMimeMail(): ilMimeMail
    {
        $this->mime_mail = new ilMimeMail();
        $this->mime_mail->From($this->senderFactory->system());

        return $this->mime_mail;
    }

    protected function initLanguageByIso2Code(string $a_code = ''): void
    {
        parent::initLanguageByIso2Code($a_code);
        $this->getLanguage()->loadLanguageModule('registration');
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('registration');
    }

    /**
     * @param int|string|ilObjUser $rcp
     * @throws ilMailException
     */
    protected function handleCurrentRecipient($rcp): void
    {
        if (is_numeric($rcp)) {
            /** @var ilObjUser $rcp */
            $rcp = ilObjectFactory::getInstanceByObjId((int) $rcp, false);
            if (!($rcp instanceof ilObjUser)) {
                throw new ilMailException('no_recipient_found');
            }
            $this->setCurrentRecipient($rcp->getEmail());
            $this->initLanguage($rcp->getId());
        } elseif (is_string($rcp) && ilUtil::is_email($rcp)) {
            $this->setCurrentRecipient($rcp);
            $this->initLanguageByIso2Code();
        } elseif ($rcp instanceof ilObjUser) {
            $this->setCurrentRecipient($rcp->getEmail());
            $this->initLanguage($rcp->getId());
        } else {
            throw new ilMailException('no_recipient_found');
        }
    }

    public function setCurrentRecipient(string $current_recipient): ilMimeMailNotification
    {
        $this->current_recipient = $current_recipient;
        return $this;
    }

    public function getCurrentRecipient(): string
    {
        return $this->current_recipient;
    }

    public function setMimeMail(ilMimeMail $mime_mail): ilMimeMailNotification
    {
        $this->mime_mail = $mime_mail;
        return $this;
    }

    public function getMimeMail(): ilMimeMail
    {
        return $this->mime_mail;
    }
}
