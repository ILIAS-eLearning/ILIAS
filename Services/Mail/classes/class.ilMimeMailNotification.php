<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMimeMail.php';
include_once 'Services/Mail/classes/class.ilMailNotification.php';

/**
 * Base class for mime mail notifications
 * @version $Id$
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
abstract class ilMimeMailNotification extends ilMailNotification
{
    protected ilMimeMail $mime_mail;
    protected string $current_recipient;

    
    public function sendMimeMail(string $a_rcp) : void
    {
        $this->mime_mail->To($a_rcp);
        $this->mime_mail->Subject($this->getSubject(), true);
        $this->mime_mail->Body($this->getBody());
        $this->mime_mail->Send();
    }

    /**
     * @return ilMimeMail
     */
    protected function initMimeMail() : \ilMimeMail
    {
        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

        $this->mime_mail = new ilMimeMail();
        $this->mime_mail->From($senderFactory->system());

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
        require_once 'Services/Mail/exceptions/class.ilMailException.php';
        
        if (is_numeric($rcp)) {
            /**
             * @var $rcp ilObjUser
             */
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
            /**
             * @var $rcp ilObjUser
             */
            $this->setCurrentRecipient($rcp->getEmail());
            $this->initLanguage($rcp->getId());
        } else {
            throw new ilMailException('no_recipient_found');
        }
    }

    /**
     * @return ilMimeMailNotification
     */
    public function setCurrentRecipient(string $current_recipient) : \ilMimeMailNotification
    {
        $this->current_recipient = $current_recipient;
        return $this;
    }

    
    public function getCurrentRecipient() : string
    {
        return $this->current_recipient;
    }

    /**
     * @return ilMimeMailNotification
     */
    public function setMimeMail(ilMimeMail $mime_mail) : \ilMimeMailNotification
    {
        $this->mime_mail = $mime_mail;
        return $this;
    }

    /**
     * @return ilMimeMail
     */
    public function getMimeMail() : \ilMimeMail
    {
        return $this->mime_mail;
    }
}
