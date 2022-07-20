<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilForumMailEventNotificationSender extends ilMailNotification
{
    const TYPE_THREAD_DELETED = 54;

    const TYPE_POST_NEW = 60;

    const TYPE_POST_ACTIVATION = 61;

    const TYPE_POST_UPDATED = 62;

    const TYPE_POST_CENSORED = 63;

    const TYPE_POST_DELETED = 64;

    const TYPE_POST_ANSWERED = 65;

    const TYPE_POST_UNCENSORED = 66;

    const PERMANENT_LINK_POST = 'PL_Post';

    const PERMANENT_LINK_FORUM = 'PL_Forum';

    /**
     * @var bool
     */
    protected $is_cronjob = false;

    /**
     * @var ilForumNotificationMailData
     */
    protected $provider;

    /**
     * @var \ilLogger
     */
    protected $logger;

    /**
     * ilForumMailNotification constructor.
     * @param ilForumNotificationMailData $provider
     * @param ilLogger                    $logger
     */
    public function __construct(ilForumNotificationMailData $provider, \ilLogger $logger)
    {
        parent::__construct(false);
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function initMail() : ilMail
    {
        $mail = parent::initMail();
        $this->logger->debug('Initialized mail service');

        return $mail;
    }

    /**
     * @inheritdoc
     */
    public function sendMail(array $a_rcp, $a_parse_recipients = true)
    {
        $this->logger->debug(sprintf(
            'Delegating notification transport to mail service for recipient "%s" ...',
            $a_rcp
        ));
        parent::sendMail($a_rcp, $a_parse_recipients);
        $this->logger->debug('Notification transport delegated');
    }

    /**
     * @inheritdoc
     */
    protected function setSubject($a_subject)
    {
        $value = parent::setSubject($a_subject);
        $this->logger->debug(sprintf('Setting subject to: %s', $a_subject));

        return $value;
    }

    /**
     * @return bool
     * @throws ilException
     */
    public function send()
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $lng = $DIC->language();

        if (!$ilSetting->get('forum_notification', 0)) {
            $this->logger->debug('Forum notifications are globally disabled');

            return false;
        }

        if (!$this->getRecipients()) {
            $this->logger->debug('No notification recipients, nothing to do');

            return false;
        }

        $lng->loadLanguageModule('forum');

        $date_type = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $mailObjects = array();

        switch ($this->getType()) {
            case self::TYPE_THREAD_DELETED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('thread_deleted_by'),
                        $this->provider->getDeletedBy(),
                        $this->provider->getForumTitle()
                    );

                    $mailObjects[] = $this->createMailValueObjectWithoutAttachments(
                        'frm_noti_subject_del_thread',
                        (int) $rcp,
                        (string) $customText,
                        'content_deleted_thread'
                    );
                }
                break;

            case self::TYPE_POST_NEW:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('frm_noti_new_post'),
                        $this->provider->getForumTitle()
                    );

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_new_post',
                        (int) $rcp,
                        (string) $customText,
                        'new_post'
                    );
                }
                break;

            case self::TYPE_POST_ACTIVATION:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = $this->getLanguageText('forums_post_activation_mail');

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_act_post',
                        (int) $rcp,
                        (string) $customText,
                        'new_post'
                    );
                }
                break;

            case self::TYPE_POST_ANSWERED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = $this->getLanguageText('forum_post_replied');

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_answ_post',
                        (int) $rcp,
                        (string) $customText,
                        'new_post'
                    );
                }
                break;

            case self::TYPE_POST_UPDATED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('post_updated_by'),
                        $this->provider->getPostUpdateUserName($this->getLanguage()),
                        $this->provider->getForumTitle()
                    );
                    $date = $this->provider->getPostUpdate();

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_upt_post',
                        (int) $rcp,
                        (string) $customText,
                        'content_post_updated',
                        $date
                    );
                }
                break;

            case self::TYPE_POST_CENSORED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('post_censored_by'),
                        $this->provider->getPostUpdateUserName($this->getLanguage()),
                        $this->provider->getForumTitle()
                    );
                    $date = $this->provider->getPostCensoredDate();

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_cens_post',
                        (int) $rcp,
                        (string) $customText,
                        'content_censored_post',
                        $date
                    );
                }
                break;

            case self::TYPE_POST_UNCENSORED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('post_uncensored_by'),
                        $this->provider->getPostUpdateUserName($this->getLanguage())
                    );
                    $date = $this->provider->getPostCensoredDate();

                    $mailObjects[] = $this->createMailValueObjectsWithAttachments(
                        'frm_noti_subject_uncens_post',
                        (int) $rcp,
                        (string) $customText,
                        'forums_the_post',
                        $date
                    );
                }
                break;

            case self::TYPE_POST_DELETED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $customText = sprintf(
                        $this->getLanguageText('post_deleted_by'),
                        $this->provider->getDeletedBy(),
                        $this->provider->getForumTitle()
                    );

                    $mailObjects[] = $this->createMailValueObjectWithoutAttachments(
                        'frm_noti_subject_del_post',
                        (int) $rcp,
                        (string) $customText,
                        'content_deleted_post'
                    );
                }
                break;
        }

        $contextId = \ilMailFormCall::getContextId();
        if (null === $contextId) {
            $contextId = '';
        }

        $contextParameters = ilMailFormCall::getContextParameters();
        if (is_array($contextParameters)) {
            $contextParameters = array();
        }

        $processor = new ilMassMailTaskProcessor();

        $processor->run(
            $mailObjects,
            ANONYMOUS_USER_ID,
            $contextId,
            $contextParameters
        );

        ilDatePresentation::setLanguage($lng);
        ilDatePresentation::setUseRelativeDates($date_type);

        return true;
    }

    /**
     * @param int $a_usr_id
     */
    protected function initLanguage($a_usr_id)
    {
        parent::initLanguage($a_usr_id);
        $this->language->loadLanguageModule('forum');
    }

    /**
     * @return boolean
     */
    public function isCronjob()
    {
        return (bool) $this->is_cronjob;
    }

    /**
     * @param boolean $is_cronjob
     */
    public function setIsCronjob($is_cronjob)
    {
        $this->is_cronjob = (bool) $is_cronjob;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getPermanentLink($type = self::PERMANENT_LINK_POST)
    {
        global $DIC;
        $ilClientIniFile = $DIC['ilClientIniFile'];

        if ($type == self::PERMANENT_LINK_FORUM) {
            $language_text = $this->getLanguageText("forums_notification_show_frm");
            $forum_parameters = $this->provider->getRefId();
        } else {
            $language_text = $this->getLanguageText("forums_notification_show_post");
            $forum_parameters = $this->provider->getRefId() . "_" . $this->provider->getThreadId() . "_" . $this->provider->getPostId();
        }

        $this->logger->debug(sprintf(
            'Building permanent with parameters %s',
            $forum_parameters
        ));

        if ($this->isCronjob()) {
            $posting_link = sprintf(
                $language_text,
                ilUtil::_getHttpPath() . "/goto.php?target=frm_" . $forum_parameters . '&client_id=' . CLIENT_ID
            ) . "\n\n";

            $posting_link .= sprintf(
                $this->getLanguageText("forums_notification_intro"),
                $ilClientIniFile->readVariable("client", "name"),
                ilUtil::_getHttpPath() . '/?client_id=' . CLIENT_ID
            ) . "\n\n";
        } else {
            $posting_link = sprintf(
                $language_text,
                ilUtil::_getHttpPath() . "/goto.php?target=frm_" . $forum_parameters . '&client_id=' . CLIENT_ID
            ) . "\n\n";

            $posting_link .= sprintf(
                $this->getLanguageText("forums_notification_intro"),
                $ilClientIniFile->readVariable("client", "name"),
                ilUtil::_getHttpPath() . '/?client_id=' . CLIENT_ID
            ) . "\n\n";
        }

        $this->logger->debug(sprintf(
            'Link built: %s',
            $posting_link
        ));

        return $posting_link;
    }

    /**
     * @return string
     */
    private function getPostMessage() : string
    {
        $pos_message = $this->provider->getPostMessage();
        if (strip_tags($pos_message) !== $pos_message) {
            $pos_message = preg_replace("/\n/i", "", $pos_message);
            $pos_message = preg_replace("/<li([^>]*)>/i", "\n<li$1>", $pos_message);
            $pos_message = preg_replace("/<\/ul([^>]*)>(?!\s*?(<p|<ul))/i", "</ul$1>\n", $pos_message);
            $pos_message = preg_replace("/<br(\s*)(\/?)>/i", "\n", $pos_message);
            $pos_message = preg_replace("/<p([^>]*)>/i", "\n\n", $pos_message);
            $pos_message = preg_replace("/<\/p([^>]*)>/i", '', $pos_message);

            return $pos_message;
        }

        return $pos_message;
    }

    /**
     * Add body and send mail with attachments
     *
     * @param string      $subjectLanguageId - Language id of subject
     * @param int         $recipientUserId   - id of the user recipient of the mail
     * @param string      $customText        - mail text after salutation
     * @param string      $action            - Language id of action
     * @param string|null $date              - date to be added in mail
     * @return ilMailValueObject
     */
    private function createMailValueObjectsWithAttachments(
        string $subjectLanguageId,
        int $recipientUserId,
        string $customText,
        string $action,
        string $date = ''
    ) {
        $subjectText = $this->createSubjectText($subjectLanguageId);

        $bodyText = $this->createMailBodyText(
            $subjectLanguageId,
            $recipientUserId,
            $customText,
            $action,
            $date
        );

        $attachmentText = $this->createAttachmentText();
        $bodyText .= $attachmentText;

        $attachmentText = $this->createAttachmentLinkText();
        $bodyText .= $attachmentText;

        $mailObject = new ilMailValueObject(
            '',
            ilObjUser::_lookupLogin($recipientUserId),
            '',
            '',
            $subjectText,
            $bodyText,
            $this->provider->getAttachments(),
            false,
            false
        );

        return $mailObject;
    }

    /**
     * Add body and send mail without attachments
     *
     * @param string      $subjectLanguageId - Language id of subject
     * @param int         $recipientUserId
     * @param string      $customText        - mail text after salutation
     * @param string      $action            - Language id of action
     * @param string|null $date              - date to be added in mail
     * @return ilMailValueObject
     */
    private function createMailValueObjectWithoutAttachments(
        string $subjectLanguageId,
        int $recipientUserId,
        string $customText,
        string $action,
        string $date = ''
    ) {
        $subjectText = $this->createSubjectText($subjectLanguageId);

        $bodyText = $this->createMailBodyText(
            $subjectLanguageId,
            $recipientUserId,
            $customText,
            $action,
            $date
        );

        $mailObject = new ilMailValueObject(
            '',
            ilObjUser::_lookupLogin($recipientUserId),
            '',
            '',
            $subjectText,
            $bodyText,
            [],
            false,
            false
        );

        return $mailObject;
    }

    private function createMailBodyText(
        string $subject,
        int $userId,
        string $customText,
        string $action,
        string $date
    ) {
        $date = $this->createMailDate($date);

        $this->addMailSubject($subject);

        $body = ilMail::getSalutation($userId, $this->getLanguage());

        $body .= "\n\n";
        $body .= $customText;
        $body .= "\n\n";
        $body .= $this->getLanguageText('forum') . ": " . $this->provider->getForumTitle();
        $body .= "\n\n";
        if ($this->provider->providesClosestContainer()) {
            $body .= $this->getLanguageText('obj_' . $this->provider->closestContainer()->getType()) . ": " . $this->provider->closestContainer()->getTitle();
            $body .= "\n\n";
        }
        $body .= $this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle();
        $body .= "\n\n";
        $body .= $this->getLanguageText($action) . ": \n------------------------------------------------------------\n";

        $body .= $this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage());
        $body .= "\n";
        $body .= $this->getLanguageText('date') . ": " . $date;
        $body .= "\n";
        $body .= $this->getLanguageText('subject') . ": " . $this->provider->getPostTitle();
        $body .= "\n";
        $body .= $this->getLanguageText('frm_noti_message');
        $body .= "\n";

        $message = strip_tags($this->getPostMessage());

        if ($this->provider->getPostCensored() == 1) {
            $message = $this->provider->getCensorshipComment();
        }

        $body .= $message . "\n";
        $body .= "------------------------------------------------------------\n";

        return $body;
    }

    private function createAttachmentText()
    {
        $attachmentText = '';
        if (count($this->provider->getAttachments()) > 0) {
            $this->logger->debug('Adding attachments ...');
            foreach ($this->provider->getAttachments() as $attachment) {
                $attachmentText .= $this->getLanguageText('attachment') . ": " . $attachment . "\n";
            }
            $attachmentText .= "\n------------------------------------------------------------\n";
        }

        return $attachmentText;
    }

    private function createAttachmentLinkText()
    {
        $body = $this->getPermanentLink();
        $body .= ilMail::_getInstallationSignature();

        return $body;
    }

    /**
     * @param string $subject
     * @internal
     */
    private function addMailSubject(string $subject)
    {
        $this->initMail();

        $this->setSubject($this->createSubjectText($subject));
    }

    /**
     * @param string $date
     * @return string
     * @throws ilDateTimeException
     * @internal
     *
     */
    private function createMailDate(string $date) : string
    {
        ilDatePresentation::setLanguage($this->language);

        if ($date === '') {
            $date = $this->provider->getPostDate();
        }

        $date = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_DATETIME));

        return $date;
    }

    /**
     * @param string $subject
     * @return string
     */
    private function createSubjectText(string $subject) : string
    {
        $container_text = '';
        if ($this->provider->providesClosestContainer()) {
            $container_text = " (" . $this->getLanguageText('frm_noti_obj_' . $this->provider->closestContainer()->getType()) .
                " \"" . $this->provider->closestContainer()->getTitle() . "\")";
        }

        return sprintf(
            $this->getLanguageText($subject),
            $this->provider->getForumTitle(),
            $container_text,
            $this->provider->getThreadTitle()
        );
    }
}
