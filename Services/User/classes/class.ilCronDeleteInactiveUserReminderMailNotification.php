<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMimeMailNotification.php';
include_once './Services/Mail/classes/class.ilMimeMail.php';

/**
 * Class ilCronDeleteInactiveUserReminderMailNotification
 * @author Guido Vollbach <gvollbach@databay.de>
 * @version $Id$
 * @package Services/User
 */
class ilCronDeleteInactiveUserReminderMailNotification extends ilMimeMailNotification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function initLanguage($a_usr_id)
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('user');
    }

    public function send()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $additional_information = $this->getAdditionalInformation();

        $old_val = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        foreach ($this->getRecipients() as $rcp) {
            try {
                $this->handleCurrentRecipient($rcp);
            } catch (ilMailException $e) {
                continue;
            }

            $this->initMimeMail();

            ilDatePresentation::setLanguage($this->getLanguage());
            $date_for_deletion = ilDatePresentation::formatDate(new ilDate($additional_information["date"], IL_CAL_UNIX));

            $this->setSubject($this->getLanguage()->txt('del_mail_subject'));
            $body = sprintf($this->getLanguage()->txt("del_mail_body"), $rcp->fullname, "\n\n", $additional_information["www"], $date_for_deletion);
            $this->appendBody($body);
            $this->appendBody(ilMail::_getInstallationSignature());
            $this->sendMimeMail($this->getCurrentRecipient());
        }

        ilDatePresentation::setUseRelativeDates($old_val);
        ilDatePresentation::setLanguage($lng);
    }
}
