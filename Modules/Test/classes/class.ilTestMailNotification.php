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

        $this->sendMail(array($owner_id));
    }

    /**
     * send an advanced notification to the owner of the test
     * @param int $owner_id
     * @param string $title
     * @param string $usr_data
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

        $this->sendMail(array($owner_id));
    }
}
