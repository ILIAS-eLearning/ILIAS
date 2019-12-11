<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceMembershipMailNotification extends ilMailNotification
{
    // v Notifications affect members & co. v
    const TYPE_ADMISSION_MEMBER = 20;
    const TYPE_DISMISS_MEMBER 	= 21;
    const TYPE_ACCEPTED_SUBSCRIPTION_MEMBER = 22;
    const TYPE_REFUSED_SUBSCRIPTION_MEMBER = 23;
    const TYPE_STATUS_CHANGED = 24;
    const TYPE_BLOCKED_MEMBER = 25;
    const TYPE_UNBLOCKED_MEMBER = 26;
    const TYPE_UNSUBSCRIBE_MEMBER = 27;
    const TYPE_SUBSCRIBE_MEMBER = 28;
    const TYPE_WAITING_LIST_MEMBER = 29;

    // v Notifications affect admins v
    const TYPE_NOTIFICATION_REGISTRATION = 30;
    const TYPE_NOTIFICATION_REGISTRATION_REQUEST = 31;
    const TYPE_NOTIFICATION_UNSUBSCRIBE = 32;

    /**
     * Notifications which are not affected by "mail_grp_member_notification"
     * setting because they addresses admins
     */
    protected $permanent_enabled_notifications = array(
        self::TYPE_NOTIFICATION_REGISTRATION,
        self::TYPE_NOTIFICATION_REGISTRATION_REQUEST,
        self::TYPE_NOTIFICATION_UNSUBSCRIBE
    );

    private $force_sending_mail = false;

    /**
     * @var ilLogger
     */
    protected $logger;

    public function __construct(ilLogger $logger, ilSetting $settings)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->settings = $settings;
    }

    public function forceSendingMail(bool $status)
    {
        $this->force_sending_mail = $status;
    }

    public function send() : bool
    {
        if (!$this->isNotificationTypeEnabled($this->getType())) {
            $this->logger->lso()->info('Membership mail disabled globally.');
            return false;
        }

        switch ($this->getType()) {
            case self::TYPE_ADMISSION_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('lso_mail_admission_new_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('lso_mail_admission_new_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('lso_mail_permanent_link')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_DISMISS_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('lso_mail_dismiss_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('lso_mail_dismiss_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_NOTIFICATION_REGISTRATION:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('lso_mail_notification_reg_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $info = $this->getAdditionalInformation();
                    $this->appendBody(sprintf(
                        $this->getLanguageText('lso_mail_notification_reg_bod'),
                        $this->userToString($info['usr_id']),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('lso_mail_permanent_link')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->createPermanentLink(array(), '_mem')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText(
                        'lso_notification_explanation_admin'
                    ));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_UNSUBSCRIBE_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('lso_mail_unsubscribe_member_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('lso_mail_unsubscribe_member_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_NOTIFICATION_UNSUBSCRIBE:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('lso_mail_notification_unsub_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $info = $this->getAdditionalInformation();
                    $this->appendBody(sprintf(
                        $this->getLanguageText('lso_mail_notification_unsub_bod'),
                        $this->userToString($info['usr_id']),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText(
                            'lso_mail_notification_unsub_bod2'
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink(
                        array(),
                        '_mem'
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText(
                            'lso_notification_explanation_admin'
                        )
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_SUBSCRIBE_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_subscribe_member_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('grp_mail_subscribe_member_bod'),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('grp_mail_permanent_link')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_NOTIFICATION_REGISTRATION_REQUEST:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_notification_reg_req_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $info = $this->getAdditionalInformation();
                    $this->appendBody(sprintf(
                        $this->getLanguageText('grp_mail_notification_reg_req_bod'),
                        $this->userToString($info['usr_id']),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('grp_mail_notification_reg_req_bod2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), '_mem'));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('grp_notification_explanation_admin')
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_REFUSED_SUBSCRIPTION_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_sub_dec_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('grp_mail_sub_dec_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_sub_acc_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('grp_mail_sub_acc_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('grp_mail_permanent_link')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_WAITING_LIST_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_wl_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $info = $this->getAdditionalInformation();
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('grp_mail_wl_bod'),
                        $this->getObjectTitle(),
                        $info['position']
                    ));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
            case self::TYPE_STATUS_CHANGED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(sprintf(
                        $this->getLanguageText('grp_mail_status_sub'),
                        $this->getObjectTitle(true)
                    ));
                    $this->setBody(ilMail::getSalutation(
                        $rcp,
                        $this->getLanguage()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('grp_mail_status_bod'),
                        $this->getObjectTitle()
                    ));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createLearningSequenceStatus((int) $rcp));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('grp_mail_permanent_link')
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
        }
        return true;
    }

    protected function initLanguage($usr_id)
    {
        parent::initLanguage($usr_id);
        $this->getLanguage()->loadLanguageModule('lso');
    }

    protected function createLearningSequenceStatus(int $usr_id) : string
    {
        $part = ilLearningSequenceParticipants::_getInstanceByObjId($this->getObjId());
        $body = $this->getLanguageText('lso_new_status') . "\n";
        $body .= $this->getLanguageText('role') . ': ';

        if ($part->isAdmin($usr_id)) {
            $body .= $this->getLanguageText('il_lso_admin') . "\n";
        } else {
            $body .= $this->getLanguageText('il_lso_member') . "\n";
        }

        if ($part->isAdmin($usr_id)) {
            $body .= $this->getLanguageText('lso_notification') . ': ';

            if ($part->isNotificationEnabled($usr_id)) {
                $body .= $this->getLanguageText('lso_notify_on') . "\n";
            } else {
                $body .= $this->getLanguageText('lso_notify_off') . "\n";
            }
        }

        return $body;
    }

    protected function isNotificationTypeEnabled(int $type) : bool
    {
        return (
            $this->force_sending_mail ||
            $this->settings->get('mail_lso_member_notification', true) ||
            in_array($type, $this->permanent_enabled_notifications)
            );
    }
}
