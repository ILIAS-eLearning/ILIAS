<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function sendMimeMail(string $a_rcp) : void
    {
        global $DIC;
        $this->mime_mail->To($a_rcp);
        $this->mime_mail->Subject($this->getSubject(), true);
        $this->mime_mail->Body($this->getBody());
        $this->mime_mail->Send();
        $this->senderFactory = $DIC["mail.mime.sender.factory"];
    }

    protected function initMimeMail() : ilMimeMail
    {
        $this->mime_mail = new ilMimeMail();
        $this->mime_mail->From($this->senderFactory->system());

        return $this->mime_mail;
    }
    
    protected function initLanguageByIso2Code(string $a_code = '') : void
    {
        parent::initLanguageByIso2Code($a_code);
        $this->getLanguage()->loadLanguageModule('registration');
    }
    
    protected function initLanguage(int $a_usr_id) : void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('registration');
    }

    /**
     * @throws ilMailException
     */
    protected function handleCurrentRecipient(string $rcp) : void
    {
        if (is_numeric($rcp)) {
            /** @var $rcp ilObjUser */
            $rcp = ilObjectFactory::getInstanceByObjId($rcp, false);
            if (!$rcp) {
                throw new ilMailException('no_recipient_found');
            }
            $this->setCurrentRecipient($rcp->getEmail());
            $this->initLanguage($rcp->getId());
        } elseif (is_string($rcp) && ilUtil::is_email($rcp)) {
            $this->setCurrentRecipient($rcp);
            $this->initLanguageByIso2Code();
        } elseif ($rcp instanceof ilObjUser) {
            /** @var $rcp ilObjUser */
            $this->setCurrentRecipient($rcp->getEmail());
            $this->initLanguage($rcp->getId());
        } else {
            throw new ilMailException('no_recipient_found');
        }
    }

    public function setCurrentRecipient(string $current_recipient) : ilMimeMailNotification
    {
        $this->current_recipient = $current_recipient;
        return $this;
    }
    
    public function getCurrentRecipient() : string
    {
        return $this->current_recipient;
    }

    public function setMimeMail(ilMimeMail $mime_mail) : ilMimeMailNotification
    {
        $this->mime_mail = $mime_mail;
        return $this;
    }

    public function getMimeMail() : ilMimeMail
    {
        return $this->mime_mail;
    }
}
