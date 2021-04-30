<?php
/* Copyright (c) 2018 Extended GPL, see docs/LICENSE */

/**
 * Class ilLearningModuleNotification class
 *
 * //TODO create an interface for notifications contract.(ilnotification?).Similar code in ilwikipage, ilblogposting
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilLearningModuleNotification
{
    const ACTION_COMMENT = "comment";
    const ACTION_UPDATE = "update";
    /**
     * @var ilObjUser
     */
    protected $ilUser;

    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $lm_set;

    /**
     * store constant value
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var ilObjLearningModule
     */
    protected $learning_module;

    /**
     * @var int
     */
    protected $page_id;

    /**
     * @var string
     */
    protected $comment;

    /**
    * @var string
    */
    protected $link;

    /**
    * @var int
    */
    protected $lm_ref_id;

    /**
    * @var string
    */
    protected $pg_title;

    /**
     * ilLearningModuleNotification constructor.
     * @param string $a_action
     * @param int $a_type  Notification type e.g. ilNotification::TYPE_LM_PAGE
     * @param ilObjLearningModule $a_learning_module
     * @param int $a_page_id
     * @param string|null $a_comment
     */
    public function __construct(string $a_action, int $a_type, ilObjLearningModule $a_learning_module, int $a_page_id, string $a_comment = null)
    {
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

    /**
     * Generate notifications and send them if necessary
     */
    public function send()
    {
        $lm_id = $this->learning_module->getId();

        // #11138  //only comment implemented so always true.
        $ignore_threshold = ($this->action == self::ACTION_COMMENT);

        $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_LM, $lm_id, "", $ignore_threshold);

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

    /**
    * Get Link to the LM page
    * @return string
    */
    protected function getLink() : string
    {
        // #15192 - should always be present
        if ($this->page_id) {
            // #18804 - see ilWikiPageGUI::preview()
            return ilLink::_getLink("", "pg", null, $this->page_id . "_" . $this->lm_ref_id);
        }

        return ilLink::_getLink($this->lm_ref_id);
    }

    /**
    * Get formatted title page
    * @return string
    */
    protected function getPageTitle() : string
    {
        return (string) ilLMPageObject::_getPresentationTitle(
            $this->page_id,
            $this->learning_module->getPageHeader(),
            $this->learning_module->isActiveNumbering(),
            $this->lm_set->get("time_scheduled_page_activation"),
            false,
            0,
            $this->lng->getLangKey()
        );
    }

    /**
    * get Subject of mail/notification
    * @param ilLanguage $ulng
    * @return string
    */
    protected function getMailSubject(ilLanguage $ulng) : string
    {
        if ($this->action == self::ACTION_COMMENT) {
            return sprintf($ulng->txt('cont_notification_comment_subject_lm'), $this->learning_module->getTitle(), $this->pg_title);
        }

        return sprintf($ulng->txt('cont_change_notification_subject_lm'), $this->learning_module->getTitle(), $this->pg_title);
    }

    /**
    * get email/notification body
    * @param ilLanguage $a_ulng
    * @param int $a_user_id
    * @return string
    */
    protected function getMailBody(ilLanguage $a_ulng, int $a_user_id) : string
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

    /**
     * Get first 500 characters of the recently added content
     * behavior copied from ilWikiUtil->sendNotification
     * @param ilLanguage $a_ulng
     * @return string
     */
    protected function getPreviewText(ilLanguage $a_ulng) : string
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
