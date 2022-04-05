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
 ********************************************************************
 */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSession
 */
class ilSessionMembershipMailNotification extends ilMailNotification
{
    public const TYPE_ADMISSION_MEMBER = 20;
    public const TYPE_DISMISS_MEMBER = 21;

    public const TYPE_ACCEPTED_SUBSCRIPTION_MEMBER = 22;
    public const TYPE_REFUSED_SUBSCRIPTION_MEMBER = 23;


    public const TYPE_BLOCKED_MEMBER = 25;
    public const TYPE_UNBLOCKED_MEMBER = 26;

    public const TYPE_UNSUBSCRIBE_MEMBER = 27;
    public const TYPE_SUBSCRIBE_MEMBER = 28;

    public const TYPE_NOTIFICATION_REGISTRATION = 30;
    public const TYPE_NOTIFICATION_REGISTRATION_REQUEST = 31;
    public const TYPE_NOTIFICATION_UNSUBSCRIBE = 32;

    public const TYPE_ENTER_NOTIFICATION = 100;
    public const TYPE_REGISTER_NOTIFICATION = 101;
    public const TYPE_UNREGISTER_NOTIFICATION = 102;

    protected ilSetting $setting;

    public function __construct()
    {
        global $DIC;

        $this->setting = $DIC->settings();

        parent::__construct();
    }

    public function send(int $userId = 0) : ?bool
    {
        $ilSetting = $this->setting;

        // parent::send();
        
        switch ($this->getType()) {
            case self::TYPE_ADMISSION_MEMBER:

                // automatic mails about status change disabled
                if (!$ilSetting->get('mail_grp_member_notification')) {
                    return null;
                }
                
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('grp_mail_admission_new_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('grp_mail_admission_new_bod'), $this->getObjectTitle())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                                        
                    $this->sendMail(array($rcp));
                }
                break;
                
            case self::TYPE_DISMISS_MEMBER:

                // automatic mails about status change disabled
                if (!$ilSetting->get('mail_grp_member_notification')) {
                    return null;
                }
                
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('grp_mail_dismiss_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('grp_mail_dismiss_bod'), $this->getObjectTitle())
                    );
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;
                
            case self::TYPE_SUBSCRIBE_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('grp_mail_subscribe_member_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('grp_mail_subscribe_member_bod'), $this->getObjectTitle())
                    );
                    
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_NOTIFICATION_REGISTRATION_REQUEST:
                
                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('grp_mail_notification_reg_req_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    
                    $info = $this->getAdditionalInformation();
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('grp_mail_notification_reg_req_bod'),
                            $this->userToString($info['usr_id']),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('grp_mail_notification_reg_req_bod2'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink([], '_mem'));
                    
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('grp_notification_explanation_admin'));
                    
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_REFUSED_SUBSCRIPTION_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('sess_mail_sub_dec_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('sess_mail_sub_dec_bod'), $this->getObjectTitle())
                    );

                    $this->getMail()->appendInstallationSignature(true);
                                        
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf($this->getLanguageText('sess_mail_sub_acc_sub'), $this->getObjectTitle(true))
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('sess_mail_sub_acc_bod'), $this->getObjectTitle())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('sess_mail_permanent_link'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink());
                    $this->getMail()->appendInstallationSignature(true);
                                        
                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_ENTER_NOTIFICATION:
                if (0 === $userId) {
                    throw new ilException('No user id given');
                }

                $userObject = ilObjectFactory::getInstanceByObjId($userId, false);
                if (!($userObject instanceof \ilObjUser)) {
                    throw new ilException(sprintf('User with ID "%s" does not exist.', $userId));
                }

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('session_mail_subject_entered'),
                            $userObject->getFullname(),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('entered_notification'),
                            $userObject->getFullname(),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('sess_mail_permanent_link_participants'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink([], '_part'));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_REGISTER_NOTIFICATION:
                if (0 === $userId) {
                    throw new ilException('No user id given');
                }

                $userObject = ilObjectFactory::getInstanceByObjId($userId, false);
                if (!($userObject instanceof \ilObjUser)) {
                    throw new ilException(sprintf('User with ID "%s" does not exist.', $userId));
                }

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('session_mail_subject_registered'),
                            $userObject->getFullname(),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('register_notification'),
                            $userObject->getFullname(),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('sess_mail_permanent_link_participants'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink([], '_part'));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_UNREGISTER_NOTIFICATION:
                if (0 === $userId) {
                    throw new ilException('No user id given');
                }

                $userObject = ilObjectFactory::getInstanceByObjId($userId, false);
                if (!($userObject instanceof \ilObjUser)) {
                    throw new ilException(sprintf('User with ID "%s" does not exist.', $userId));
                }

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('session_mail_subject_deletion'),
                            $userObject->getFullname(),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf(
                            $this->getLanguageText('deletion_notification'),
                            $userObject->getFullname(),
                            $this->getObjectTitle()
                        )
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('sess_mail_permanent_link_participants'));
                    $this->appendBody("\n\n");
                    $this->appendBody($this->createPermanentLink([], '_part'));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;
        }
        return true;
    }

    protected function initLanguage(int $a_usr_id) : void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('sess');
    }
}
