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

declare(strict_types=1);

namespace ILIAS\Administration;

use ilCtrl;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilPropertyFormGUI;
use ilTextInputGUI;
use ilEMailInputGUI;
use ilSystemSupportContacts;
use ilAccessibilitySupportContacts;
use ILIAS\UICore\GlobalTemplate;

/**
 * GUI to change the header title of the installation
 *
 * @ilCtrl_isCalledBy    ILIAS\Administration\ContactInformationGUI: ilObjGeneralSettingsGUI
 */
readonly class ContactInformationGUI
{
    public function __construct(
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private Setting $settings,
        private bool $has_write_access,
    ) {
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("view");
        switch ($cmd) {
            case 'view':
                $this->view();
                break;

            case 'update':
                if ($this->has_write_access) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function view(): void
    {
        $this->tpl->setContent($this->buildForm()->getHTML());
    }

    public function update(): void
    {
        $form = $this->buildForm();
        if ($form->checkInput()) {
            $fs = array("admin_firstname", "admin_lastname", "admin_title", "admin_position",
                        "admin_institution", "admin_street", "admin_zipcode", "admin_city",
                        "admin_country", "admin_phone", "admin_email");
            foreach ($fs as $f) {
                $this->settings->set($f, $form->getInput($f));
            }

            // System support contacts
            ilSystemSupportContacts::setList($form->getInput("adm_support_contacts"));

            // Accessibility support contacts
            ilAccessibilitySupportContacts::setList($form->getInput("accessibility_support_contacts"));

            $this->tpl->setOnScreenMessage(GlobalTemplate::MESSAGE_TYPE_SUCCESS, $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this);
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
        }
    }

    public function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // first name
        $ti = new ilTextInputGUI($this->lng->txt("firstname"), "admin_firstname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_firstname"));
        $form->addItem($ti);

        // last name
        $ti = new ilTextInputGUI($this->lng->txt("lastname"), "admin_lastname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_lastname"));
        $form->addItem($ti);

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "admin_title");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($this->settings->get("admin_title"));
        $form->addItem($ti);

        // position
        $ti = new ilTextInputGUI($this->lng->txt("position"), "admin_position");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($this->settings->get("admin_position"));
        $form->addItem($ti);

        // institution
        $ti = new ilTextInputGUI($this->lng->txt("institution"), "admin_institution");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($this->settings->get("admin_institution"));
        $form->addItem($ti);

        // street
        $ti = new ilTextInputGUI($this->lng->txt("street"), "admin_street");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_street"));
        $form->addItem($ti);

        // zip code
        $ti = new ilTextInputGUI($this->lng->txt("zipcode"), "admin_zipcode");
        $ti->setMaxLength(10);
        $ti->setSize(5);
        //$ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_zipcode"));
        $form->addItem($ti);

        // city
        $ti = new ilTextInputGUI($this->lng->txt("city"), "admin_city");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_city"));
        $form->addItem($ti);

        // country
        $ti = new ilTextInputGUI($this->lng->txt("country"), "admin_country");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_country"));
        $form->addItem($ti);

        // phone
        $ti = new ilTextInputGUI($this->lng->txt("phone"), "admin_phone");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($this->settings->get("admin_phone"));
        $form->addItem($ti);

        // email
        $ti = new ilEMailInputGUI($this->lng->txt("email"), "admin_email");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $ti->setValue($this->settings->get("admin_email"));
        $form->addItem($ti);

        // System support contacts
        $ti = new ilTextInputGUI($this->lng->txt("adm_support_contacts"), "adm_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilSystemSupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_support_contacts_info"));
        $form->addItem($ti);

        // Accessibility support contacts
        $ti = new ilTextInputGUI($this->lng->txt("adm_accessibility_contacts"), "accessibility_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilAccessibilitySupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_accessibility_contacts_info"));
        $form->addItem($ti);

        if ($this->has_write_access) {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }

        $form->setTitle($this->lng->txt("contact_data"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
}
