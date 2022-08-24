<?php

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
 * Class ilLearningModuleNotification class
 *
 * //TODO create an interface for notifications contract.(ilnotification?).Similar code in ilwikipage, ilblogposting
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilLearningModuleNotification
{
    public const ACTION_COMMENT = "comment";
    public const ACTION_UPDATE = "update";

    protected ilObjUser $ilUser;
    protected ilAccessHandler $ilAccess;
    protected ilLanguage $lng;
    protected ilSetting $lm_set;
    protected string $action;
    protected int $type;
    protected ilObjLearningModule $learning_module;
    protected int $page_id;
    protected string $comment;
    protected string $link;
    protected int $lm_ref_id;
    protected string $pg_title;

    /**
     * @param int $a_type  Notification type e.g. ilNotification::TYPE_LM_PAGE
     */
    public function __construct(
        string $a_action,
        int $a_type,
        ilObjLearningModule $a_learning_module,
        int $a_page_id,
        string $a_comment = ""
    ) {
        global $DIC;

        $this->ilUser = $DIC->user();
        $this->ilAccess = $DIC->access();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("content");
        $this->lm_set = new ilSetting("lm");
        $this->action = $a_action;
        $this->type = $a_type;
        $this->learning_module = $a_learning_module;
        $this->page_id = $a_page_id;
        $this->comment = $a_comment;
        $this->lm_ref_id = $this->learning_module->getRefId();
        $this->link = $this->getLink();
        $this->pg_title = $this->getPageTitle();
    }

    // Generate notifications and send them if necessary
    public function send(): void
    {
        $lm_id = $this->learning_module->getId();

        // #11138  //only comment implemented so always true.
        $ignore_threshold = ($this->action == self::ACTION_COMMENT);

        $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_LM, $lm_id, null, $ignore_threshold);

        if ($this->type == ilNotification::TYPE_LM_PAGE) {
            $page_users = ilNotification::getNotificationsForObject($this->type, $this->page_id, null, $ignore_threshold);
            $users = array_merge($users, $page_users);
            ilNotification::updateNotificationTime(ilNotification::TYPE_LM_PAGE, $this->page_id, $users);
        }

        if (!sizeof($users)) {
            return;
        }

        ilNotification::updateNotificationTime(ilNotification::TYPE_LM, $lm_id, $users, $this->page_id);


        foreach (array_unique($users) as $idx => $user_id) {
            if ($user_id != $this->ilUser->getId() &&
                $this->ilAccess->checkAccessOfUser($user_id, 'read', '', $this->lm_ref_id)) {
                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('content');

                $subject = $this->getMailSubject($ulng);
                $message = $this->getMailBody($ulng, $user_id);

                $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $mail_obj->enqueue(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    []
                );
            }
        }
    }

    protected function getLink(): string
    {
        // #15192 - should always be present
        if ($this->page_id) {
            // #18804 - see ilWikiPageGUI::preview()
            return ilLink::_getLink(null, "pg", [], $this->page_id . "_" . $this->lm_ref_id);
        }

        return ilLink::_getLink($this->lm_ref_id);
    }

    protected function getPageTitle(): string
    {
        return ilLMPageObject::_getPresentationTitle(
            $this->page_id,
            $this->learning_module->getPageHeader(),
            $this->learning_module->isActiveNumbering(),
            $this->lm_set->get("time_scheduled_page_activation"),
            false,
            0,
            $this->lng->getLangKey()
        );
    }

    protected function getMailSubject(ilLanguage $ulng): string
    {
        if ($this->action == self::ACTION_COMMENT) {
            return sprintf($ulng->txt('cont_notification_comment_subject_lm'), $this->learning_module->getTitle(), $this->pg_title);
        }

        return sprintf($ulng->txt('cont_change_notification_subject_lm'), $this->learning_module->getTitle(), $this->pg_title);
    }

    protected function getMailBody(ilLanguage $a_ulng, int $a_user_id): string
    {
        $message = sprintf($a_ulng->txt('cont_change_notification_salutation'), ilObjUser::_lookupFullname($a_user_id)) . "\n\n";
        $message .= $a_ulng->txt('cont_notification_' . $this->action . "_lm") . ":\n\n";
        $message .= $a_ulng->txt('learning module') . ": " . $this->learning_module->getTitle() . "\n";
        $message .= $a_ulng->txt('page') . ": " . $this->pg_title . "\n";
        if ($this->action == self::ACTION_COMMENT) {
            // include comment/note text
            $message .= $a_ulng->txt('cont_commented_by') . ": " . ilUserUtil::getNamePresentation($this->ilUser->getId()) . "\n";
            $message .= "\n" . $a_ulng->txt('comment') . ":\n\"" . trim($this->comment) . "\"\n";
        } else {
            $message .= $this->getPreviewText($a_ulng);
        }

        $message .= "\n" . $a_ulng->txt('url') . ": " . $this->link;

        return $message;
    }

    protected function getPreviewText(ilLanguage $a_ulng): string
    {
        $page = new ilLMPageGUI($this->page_id);
        $page->setRawPageContent(true);
        $page->setAbstractOnly(true);
        $page->setFileDownloadLink(".");
        $page->setFullscreenLink(".");
        $page->setSourcecodeDownloadScript(".");
        $str = $page->showPage();
        $str = ilPageObject::truncateHTML($str, 500, "...");
        // making things more readable
        $str = str_replace('<br/>', "\n", $str);
        $str = str_replace('<br />', "\n", $str);
        $str = str_replace('</p>', "\n", $str);
        $str = str_replace('</div>', "\n", $str);
        $str = trim(strip_tags($str));

        $content = "\n" . $a_ulng->txt('content') . "\n" .
            "----------------------------------------\n" .
            $str . "\n" .
            "----------------------------------------\n";

        return $content;
    }
}
