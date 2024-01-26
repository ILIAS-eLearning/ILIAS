<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMimeMailNotification.php';
include_once './Services/Mail/classes/class.ilMimeMail.php';

/**
 * Class ilMailCronOrphanedMailsNotification
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotification extends ilMimeMailNotification
{
    /**
     * {@inheritdoc}
     */
    public function __construct($a_is_personal_workspace = false)
    {
        parent::__construct($a_is_personal_workspace);
    }

    /**
     * @param string $a_code
     */
    protected function initLanguageByIso2Code($a_code = '')
    {
        parent::initLanguageByIso2Code($a_code);
        $this->getLanguage()->loadLanguageModule('user');
    }

    /**
     *
     */
    public function send()
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
            
            $body = $this->getOrphandMailsBody();
            $this->appendBody($body);
            $this->appendBody(ilMail::_getInstallationSignature());
            $this->sendMimeMail($this->getCurrentRecipient());
        }
    }

    private function buildFolderTitle(ilMailCronOrphanedMailsFolderObject $folder_object) : string
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

    public function getOrphandMailsBody()
    {
        $additional_information = $this->getAdditionalInformation();
        /** @var ilMailCronOrphanedMailsFolderObject[] $mail_folders */
        $mail_folders = $additional_information['mail_folders'];

        $folder_rendered = false;

        foreach ($mail_folders as $folder_object) {
            if ($folder_rendered) {
                $this->appendBody("\n");
            }

            $this->appendBody($this->buildFolderTitle($folder_object) . ':');
            $this->appendBody("\n");
            foreach ($folder_object->getOrphanedMailObjects() as  $mail_object) {
                $this->appendBody('- ' . $mail_object->getMailSubject() ?? $this->getLanguage()->txt('not_available'));
                $this->appendBody("\n");
            }

            $folder_rendered = true;
        }
    }
}
