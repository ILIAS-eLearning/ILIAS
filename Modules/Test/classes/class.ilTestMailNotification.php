<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * Class ilTestMailNotification
 * @author Nadia Ahmad <nahmad@databay.de>
 *
 * @version	$Id:$
 * @ingroup ModulesTest
 */
class ilTestMailNotification extends ilMailNotification
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Send a simple notification to the owner of the test
     *
     * @param int $owner_id
     * @param string $title
     * @param string $usr_data
     */
    public function sendSimpleNotification($owner_id, $title, $usr_data)
    {
        $this->initLanguage($owner_id);
        $this->language->loadLanguageModule('assessment');
        $this->initMail();
        $this->setSubject(sprintf($this->language->txt('tst_user_finished_test'), $title));
        $this->setBody(ilMail::getSalutation($owner_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($this->language->txt('user_has_finished_a_test'));
        $this->appendBody("\n\n");
        
        $this->appendBody($this->language->txt('title') . ': ' . $title);
        $this->appendBody("\n");
        $this->appendBody($this->language->txt('tst_participant') . ': ' . $usr_data);
        $this->appendBody("\n");
        
        ilDatePresentation::setUseRelativeDates(false);
        $this->appendBody($this->language->txt('tst_finished') . ': ' . ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)));
        $this->appendBody("\n\n");
        
        $this->appendBody($this->language->txt('tst_notification_explanation_admin'));
        $this->appendBody("\n");
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($owner_id), array('system'));
    }

    /**
     * send an advanced notification to the owner of the test
     * @param int $owner_id
     * @param string $title
     * @param sting $usr_data
     * @param array $file_names
     */
    public function sendAdvancedNotification($owner_id, $title, $usr_data, $file_names)
    {
        $this->initLanguage($owner_id);
        $this->language->loadLanguageModule('assessment');

        $this->initMail();
        $this->setSubject(sprintf($this->language->txt('tst_user_finished_test'), $title));
        $this->setBody(ilMail::getSalutation($owner_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($this->language->txt('user_has_finished_a_test'));
        $this->appendBody("\n\n");

        $this->appendBody($this->language->txt('title') . ': ' . $title);
        $this->appendBody("\n");
        $this->appendBody($this->language->txt('tst_participant') . ': ' . $usr_data);
        $this->appendBody("\n");

        ilDatePresentation::setUseRelativeDates(false);
        $this->appendBody($this->language->txt('tst_finished') . ': ' . ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)));
        $this->appendBody("\n\n");

        $this->appendBody($this->language->txt('tst_attached_xls_file'));
        $this->appendBody("\n\n");
        
        $this->appendBody($this->language->txt('tst_notification_explanation_admin'));
        $this->appendBody("\n");
        
        $this->setAttachments($file_names);
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($owner_id), array('system'));
    }
}
