<?php declare(strict_types=1);

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
