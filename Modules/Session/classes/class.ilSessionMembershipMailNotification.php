<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSession
 */
class ilSessionMembershipMailNotification extends ilMailNotification
{
    const TYPE_ADMISSION_MEMBER = 20;
    const TYPE_DISMISS_MEMBER 	= 21;
    
    const TYPE_ACCEPTED_SUBSCRIPTION_MEMBER = 22;
    const TYPE_REFUSED_SUBSCRIPTION_MEMBER = 23;
    
    
    const TYPE_BLOCKED_MEMBER = 25;
    const TYPE_UNBLOCKED_MEMBER = 26;
    
    const TYPE_UNSUBSCRIBE_MEMBER = 27;
    const TYPE_SUBSCRIBE_MEMBER = 28;
    
    const TYPE_NOTIFICATION_REGISTRATION = 30;
    const TYPE_NOTIFICATION_REGISTRATION_REQUEST = 31;
    const TYPE_NOTIFICATION_UNSUBSCRIBE = 32;
    

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Send notifications
     * @return
     */
    public function send()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        // parent::send();
        
        switch ($this->getType()) {
            case self::TYPE_ADMISSION_MEMBER:

                // automatic mails about status change disabled
                if (!$ilSetting->get('mail_grp_member_notification', false)) {
                    return;
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
                                        
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
                
            case self::TYPE_DISMISS_MEMBER:

                // automatic mails about status change disabled
                if (!$ilSetting->get('mail_grp_member_notification', false)) {
                    return;
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
                    $this->sendMail(array($rcp), array('system'));
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

                    $this->sendMail(array($rcp), array('system'));
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
                    $this->appendBody($this->createPermanentLink(array(), '_mem'));
                    
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('grp_notification_explanation_admin'));
                    
                    $this->getMail()->appendInstallationSignature(true);
                    $this->sendMail(array($rcp), array('system'));
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
                                        
                    $this->sendMail(array($rcp), array('system'));
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
                                        
                    $this->sendMail(array($rcp), array('system'));
                }
                break;
                
        }
        return true;
    }
    
    /**
     * Add language module crs
     * @param object $a_usr_id
     * @return
     */
    protected function initLanguage($a_usr_id)
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('sess');
    }
}
