<?php

declare(strict_types=0);
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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesMembership
 */
class ilCourseMembershipMailNotification extends ilMailNotification
{
    // v Notifications affect members & co. v
    public const TYPE_ADMISSION_MEMBER = 20;
    public const TYPE_DISMISS_MEMBER = 21;

    public const TYPE_ACCEPTED_SUBSCRIPTION_MEMBER = 22;
    public const TYPE_REFUSED_SUBSCRIPTION_MEMBER = 23;

    public const TYPE_STATUS_CHANGED = 24;

    public const TYPE_BLOCKED_MEMBER = 25;
    public const TYPE_UNBLOCKED_MEMBER = 26;

    public const TYPE_UNSUBSCRIBE_MEMBER = 27;
    public const TYPE_SUBSCRIBE_MEMBER = 28;
    public const TYPE_WAITING_LIST_MEMBER = 29;

    // v Notifications affect admins v
    public const TYPE_NOTIFICATION_REGISTRATION = 30;
    public const TYPE_NOTIFICATION_REGISTRATION_REQUEST = 31;
    public const TYPE_NOTIFICATION_UNSUBSCRIBE = 32;

    public const TYPE_NOTIFICATION_ADMINS = 33;
    public const TYPE_NOTIFICATION_ADMINS_REGISTRATION_REQUEST = 34;

    /**
     * @var array $permanent_enabled_notifications
     * Notifications which are not affected by "mail_crs_member_notification" setting
     * because they addresses admins
     */
    protected array $permanent_enabled_notifications = array(
        self::TYPE_NOTIFICATION_REGISTRATION,
        self::TYPE_NOTIFICATION_REGISTRATION_REQUEST,
        self::TYPE_NOTIFICATION_UNSUBSCRIBE
    );

    private bool $force_sending_mail = false;

    protected ilSetting $setting;

    public function __construct()
    {
        global $DIC;

        $this->setting = $DIC->settings();
        parent::__construct(false);
    }

    /**
     * @inheritDoc
     */
    protected function initMail(): ilMail
    {
        parent::initMail();
        $this->mail = $this->mail->withContextParameters([
            ilMail::PROP_CONTEXT_SUBJECT_PREFIX => ilContainer::_lookupContainerSetting(
                ilObject::_lookupObjId($this->getRefId()),
                ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX,
                ''
            ),
        ]);

        return $this->mail;
    }

    /**
     * Force sending mail independent from global setting
     */
    public function forceSendingMail(bool $a_status): void
    {
        $this->force_sending_mail = $a_status;
    }

    public function send(): bool
    {
        if (
            $this->getRefId() &&
            in_array($this->getType(), array(self::TYPE_ADMISSION_MEMBER))) {
            $obj = ilObjectFactory::getInstanceByRefId($this->getRefId());

            if ($obj->getAutoNotification() == false) {
                if (!$this->force_sending_mail) {
                    return false;
                }
            }
        }
        if (!$this->isNotificationTypeEnabled($this->getType())) {
            return false;
        }
        // #11359
        // parent::send();

        switch ($this->getType()) {
            case self::TYPE_ADMISSION_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_added_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_added_member_body'), $this->getObjectTitle())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_accept_subscriber'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_accept_subscriber_body'), $this->getObjectTitle())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_REFUSED_SUBSCRIPTION_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_reject_subscriber'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_reject_subscriber_body'), $this->getObjectTitle())
                    );

                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_STATUS_CHANGED:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_status_changed'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_status_changed_body'), $this->getObjectTitle())
                    );

                    $this->appendBody("\n\n");
                    $this->appendBody($this->createCourseStatus($rcp));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());

                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_DISMISS_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_dismiss_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_dismiss_member_body'), $this->getObjectTitle())
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_BLOCKED_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_blocked_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_blocked_member_body'), $this->getObjectTitle())
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_UNBLOCKED_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_unblocked_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_unblocked_member_body'), $this->getObjectTitle())
                    );

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_NOTIFICATION_REGISTRATION:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_new_subscription'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");

                    $info = $this->getAdditionalInformation();
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('crs_new_subscription_body'),
                            $this->userToString($info['usr_id']),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink(array(), '_mem'));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));

                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_NOTIFICATION_REGISTRATION_REQUEST:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_new_subscription_request'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");

                    $info = $this->getAdditionalInformation();
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('crs_new_subscription_request_body'),
                            $this->userToString($info['usr_id']),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_new_subscription_request_body2'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), '_mem'));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));

                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_NOTIFICATION_UNSUBSCRIBE:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_cancel_subscription'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");

                    $info = $this->getAdditionalInformation();
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('crs_cancel_subscription_body'),
                            $this->userToString($info['usr_id']),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_cancel_subscription_body2'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink(array(), '_mem'));

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_notification_explanation_admin'));

                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_UNSUBSCRIBE_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_unsubscribe_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_unsubscribe_member_body'), $this->getObjectTitle())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_unsubscribe_member_explanation'));
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_SUBSCRIBE_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_subscribe_member'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('crs_subscribe_member_body'), $this->getObjectTitle())
                    );

                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_WAITING_LIST_MEMBER:
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('crs_subscribe_wl'), $this->getObjectTitle(true))
                    );

                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));

                    $info = $this->getAdditionalInformation();
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('crs_subscribe_wl_body'),
                            $this->getObjectTitle(),
                            $info['position']
                        )
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;
        }
        return true;
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('crs');
    }

    protected function createCourseStatus(int $a_usr_id): string
    {
        $part = ilCourseParticipants::_getInstanceByObjId($this->getObjId());

        $body = $this->getLanguageText('crs_new_status') . "\n";
        $body .= $this->getLanguageText('role') . ': ';

        if ($part->isAdmin($a_usr_id)) {
            $body .= $this->getLanguageText('crs_admin') . "\n";
        } elseif ($part->isTutor($a_usr_id)) {
            $body .= $this->getLanguageText('crs_tutor') . "\n";
        } else {
            $body .= $this->getLanguageText('crs_member') . "\n";
        }

        if ($part->isAdmin($a_usr_id) || $part->isTutor($a_usr_id)) {
            $body .= $this->getLanguageText('crs_status') . ': ';

            if ($part->isNotificationEnabled($a_usr_id)) {
                $body .= $this->getLanguageText('crs_notify') . "\n";
            } else {
                $body .= $this->getLanguageText('crs_no_notify') . "\n";
            }
        } else {
            $body .= $this->getLanguageText('crs_access') . ': ';

            if ($part->isBlocked($a_usr_id)) {
                $body .= $this->getLanguageText('crs_blocked') . "\n";
            } else {
                $body .= $this->getLanguageText('crs_unblocked') . "\n";
            }
        }

        $body .= $this->getLanguageText('crs_passed') . ': ';

        if ($part->hasPassed($a_usr_id)) {
            $body .= $this->getLanguageText('yes');
        } else {
            $body .= $this->getLanguageText('no');
        }
        return $body;
    }

    /**
     * get setting "mail_crs_member_notification" and excludes types which are not affected by this setting
     * See description of $this->permanent_enabled_notifications
     */
    protected function isNotificationTypeEnabled(int $a_type): bool
    {
        return
            $this->force_sending_mail ||
            $this->setting->get('mail_crs_member_notification', '1') ||
            in_array($a_type, $this->permanent_enabled_notifications);
    }
}
