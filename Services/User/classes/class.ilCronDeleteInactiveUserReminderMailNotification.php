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
 * Class ilCronDeleteInactiveUserReminderMailNotification
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilCronDeleteInactiveUserReminderMailNotification extends ilMimeMailNotification
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function initLanguageByIso2Code(string $a_code = '') : void
    {
        parent::initLanguageByIso2Code($a_code);
        $this->getLanguage()->loadLanguageModule('user');
    }

    public function send() : void
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
            $this->initLanguageByIso2Code();

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
