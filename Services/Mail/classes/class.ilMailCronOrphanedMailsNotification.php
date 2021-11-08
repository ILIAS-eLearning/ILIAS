<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailCronOrphanedMailsNotification
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotification extends ilMimeMailNotification
{
    protected function initLanguageByIso2Code(string $a_code = '') : void
    {
        parent::initLanguageByIso2Code($a_code);
        $this->getLanguage()->loadLanguageModule('user');
    }

    public function send() : void
    {
        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException $e) {
                continue;
            }
            $this->initMimeMail();
            $this->initLanguageByIso2Code();
            $this->setSubject($this->getLanguage()->txt('orphaned_mail_subject'));
            
            $this->appendBody(ilMail::getSalutation($rcp));
            $this->appendBody("\n\n");
            $this->appendBody($this->getLanguage()->txt('orphaned_mail_body'));
            $this->appendBody("\n\n");
            
            $this->appendOrphandMailsBody();

            $this->appendBody(ilMail::_getInstallationSignature());
            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }
    
    public function appendOrphandMailsBody() : void
    {
        $additional_information = $this->getAdditionalInformation();
        $mail_folders = $additional_information['mail_folders'];
        
        foreach ($mail_folders as $folder_object) {
            $folder_title = $this->getLanguage()->txt('mail_' . $folder_object->getFolderTitle());
            $this->appendBody($folder_title . ':');
            $this->appendBody("\n");
            foreach ($folder_object->getOrphanedMailObjects() as  $mail_object) {
                $this->appendBody($mail_object->getMailSubject());
                $this->appendBody("\n");
            }
        }
    }
}
