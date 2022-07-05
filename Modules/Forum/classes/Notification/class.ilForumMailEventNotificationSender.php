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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilForumMailEventNotificationSender extends ilMailNotification
{
    private const TYPE_THREAD_DELETED = 54;
    private const TYPE_POST_NEW = 60;
    private const TYPE_POST_ACTIVATION = 61;
    private const TYPE_POST_UPDATED = 62;
    private const TYPE_POST_CENSORED = 63;
    private const TYPE_POST_DELETED = 64;
    private const TYPE_POST_ANSWERED = 65;
    private const TYPE_POST_UNCENSORED = 66;
    private const PERMANENT_LINK_POST = 'PL_Post';
    private const PERMANENT_LINK_FORUM = 'PL_Forum';

    protected ilForumNotificationMailData $provider;
    protected ilLogger $logger;
    protected bool $is_cronjob = false;

    public function __construct(ilForumNotificationMailData $provider, ilLogger $logger)
    {
        parent::__construct(false);
        $this->provider = $provider;
        $this->logger = $logger;
    }

    protected function initMail() : ilMail
    {
        $mail = parent::initMail();
        $this->logger->debug('Initialized mail service');

        return $mail;
    }

    public function sendMail(array $a_rcp, bool $a_parse_recipients = true) : void
    {
        $this->logger->debug(sprintf(
            'Delegating notification transport to mail service for recipient "%s" ...',
            json_encode($a_rcp, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        ));
        parent::sendMail($a_rcp, $a_parse_recipients);
        $this->logger->debug('Notification transport delegated');
    }

    protected function setSubject(string $a_subject) : string
    {
        $value = parent::setSubject($a_subject);
        $this->logger->debug(sprintf('Setting subject to: %s', $a_subject));

        return $value;
    }

    public function send() : bool
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $lng = $DIC->language();

        if (!$ilSetting->get('forum_notification', '0')) {
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

        $mailObjects = [];

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
                        $customText,
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
                        $customText,
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
                        $customText,
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
                        $customText,
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
                        $customText,
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
                        $customText,
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
                        $customText,
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
                        $customText,
                        'content_deleted_post'
                    );
                }
                break;
        }

        $contextId = ilMailFormCall::getContextId();
        if (null === $contextId) {
            $contextId = '';
        }

        $contextParameters = ilMailFormCall::getContextParameters();

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

    protected function initLanguage(int $a_usr_id) : void
    {
        parent::initLanguage($a_usr_id);
        $this->language->loadLanguageModule('forum');
    }

    public function isCronjob() : bool
    {
        return $this->is_cronjob;
    }

    public function setIsCronjob(bool $is_cronjob) : void
    {
        $this->is_cronjob = $is_cronjob;
    }

    private function getPermanentLink(string $type = self::PERMANENT_LINK_POST) : string
    {
        global $DIC;
        $ilClientIniFile = $DIC['ilClientIniFile'];

        if ($type === self::PERMANENT_LINK_FORUM) {
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

        $posting_link = sprintf(
            $language_text,
            ilUtil::_getHttpPath() . "/goto.php?target=frm_" . $forum_parameters . '&client_id=' . CLIENT_ID
        ) . "\n\n";
        $posting_link .= sprintf(
            $this->getLanguageText("forums_notification_intro"),
            $ilClientIniFile->readVariable("client", "name"),
            ilUtil::_getHttpPath() . '/?client_id=' . CLIENT_ID
        ) . "\n\n";

        $this->logger->debug(sprintf(
            'Link built: %s',
            $posting_link
        ));

        return $posting_link;
    }

    private function getPostMessage() : string
    {
        $pos_message = $this->provider->getPostMessage();
        if (strip_tags($pos_message) !== $pos_message) {
            $pos_message = preg_replace("/\n/i", "", $pos_message);
            $pos_message = preg_replace("/<li([^>]*)>/i", "\n<li$1>", $pos_message);
            $pos_message = preg_replace("/<\/ul([^>]*)>(?!\s*?(<p|<ul))/i", "</ul$1>\n", $pos_message);
            $pos_message = preg_replace("/<br(\s*)(\/?)>/i", "\n", $pos_message);
            $pos_message = preg_replace("/<p([^>]*)>/i", "\n\n", $pos_message);
            return preg_replace("/<\/p([^>]*)>/i", '', $pos_message);
        }

        return $pos_message;
    }

    /**
     * Add body and send mail with attachments
     * @param string $subjectLanguageId - Language id of subject
     * @param int    $recipientUserId   - id of the user recipient of the mail
     * @param string $customText        - mail text after salutation
     * @param string $action            - Language id of action
     * @param string $date              - date to be added in mail
     * @return ilMailValueObject
     */
    private function createMailValueObjectsWithAttachments(
        string $subjectLanguageId,
        int $recipientUserId,
        string $customText,
        string $action,
        string $date = ''
    ) : ilMailValueObject {
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

        return new ilMailValueObject(
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
    }

    /**
     * Add body and send mail without attachments
     * @param string $subjectLanguageId - Language id of subject
     * @param int    $recipientUserId
     * @param string $customText        - mail text after salutation
     * @param string $action            - Language id of action
     * @param string $date              - date to be added in mail
     * @return ilMailValueObject
     */
    private function createMailValueObjectWithoutAttachments(
        string $subjectLanguageId,
        int $recipientUserId,
        string $customText,
        string $action,
        string $date = ''
    ) : ilMailValueObject {
        $subjectText = $this->createSubjectText($subjectLanguageId);

        $bodyText = $this->createMailBodyText(
            $subjectLanguageId,
            $recipientUserId,
            $customText,
            $action,
            $date
        );

        return new ilMailValueObject(
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
    }

    private function createMailBodyText(
        string $subject,
        int $userId,
        string $customText,
        string $action,
        string $date
    ) : string {
        $date = $this->createMailDate($date);

        $this->addMailSubject($subject);

        $body = ilMail::getSalutation($userId, $this->getLanguage());

        $body .= "\n\n";
        $body .= $customText;
        $body .= "\n\n";
        $body .= $this->getLanguageText('forum') . ": " . $this->provider->getForumTitle();
        $body .= "\n\n";
        $body .= $this->getLanguageText($this->provider->getTopItemType()) . ": " . $this->provider->getTopItemTitle();
        $body .= "\n\n";
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

        if ($this->provider->isPostCensored()) {
            $message = $this->provider->getCensorshipComment();
        }

        $body .= $message . "\n";
        $body .= "------------------------------------------------------------\n";

        return $body;
    }

    private function createAttachmentText() : string
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

    private function createAttachmentLinkText() : string
    {
        $body = $this->getPermanentLink();
        $body .= ilMail::_getInstallationSignature();

        return $body;
    }

    private function addMailSubject(string $subject) : void
    {
        $this->initMail();

        $this->setSubject($this->createSubjectText($subject));
    }

    private function createMailDate(string $date) : string
    {
        ilDatePresentation::setLanguage($this->language);

        if ($date === '') {
            $date = $this->provider->getPostDate();
        }

        return ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_DATETIME));
    }

    private function createSubjectText(string $subject) : string
    {
        return sprintf(
            $this->getLanguageText($subject),
            $this->provider->getForumTitle(),
            $this->getLanguageText($this->provider->getTopItemType()),
            $this->provider->getTopItemTitle(),
            $this->provider->getThreadTitle()
        );
    }
}
