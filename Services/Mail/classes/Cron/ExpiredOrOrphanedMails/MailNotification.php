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

namespace ILIAS\Mail\Cron\ExpiredOrOrphanedMails;

use ilMimeMailNotification;
use ilMailException;
use ilMail;

class MailNotification extends ilMimeMailNotification
{
    private function buildFolderTitle(FolderDto $folder_object) : string
    {
        $folder_title = $folder_object->getFolderTitle();
        $folder_translation = $this->getLanguage()->txt('deleted');

        if ($folder_title !== null && $folder_title !== '') {
            $lang_key = 'mail_' . $folder_title;
            $folder_translation = $this->getLanguage()->txt($lang_key);

            if ($folder_translation === '-' . $lang_key . '-') {
                $folder_translation = $folder_title;
            }
        }

        return $folder_translation;
    }

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

            $this->appendOrphanedMailsBody();

            $this->appendBody(ilMail::_getInstallationSignature());
            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }

    public function appendOrphanedMailsBody() : void
    {
        $additional_information = $this->getAdditionalInformation();
        /** @var FolderDto[] $mail_folders */
        $mail_folders = $additional_information['mail_folders'];

        $folder_rendered = false;

        foreach ($mail_folders as $folder_object) {
            if ($folder_rendered) {
                $this->appendBody("\n");
            }

            $this->appendBody($this->buildFolderTitle($folder_object) . ':');
            $this->appendBody("\n");
            foreach ($folder_object->getOrphanedMailObjects() as $mail_object) {
                $this->appendBody('- ' . $mail_object->getMailSubject() ?? $this->getLanguage()->txt('not_available'));
                $this->appendBody("\n");
            }

            $folder_rendered = true;
        }
    }
}
