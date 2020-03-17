<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilSCORM2004TrackingItemsPerUserFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004TrackingItemsPerUserFilterGUI extends ilPropertyFormGUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function parse($userSelected, $report, $reports)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));

        include_once "Services/Tracking/classes/class.ilTrQuery.php";
        $users = ilTrQuery::getParticipantsForObject($this->parent_obj->object->ref_id);

        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        if (count($users) > 0) {
            foreach ($users as $user) {
                if (ilObject::_exists($user) && ilObject::_lookUpType($user) == 'usr') {
                    if ($allowExportPrivacy == true) {
                        $e_user = new ilObjUser($user);
                        $options[$user] = $e_user->getLastname() . ", " . $e_user->getFirstname();
                    } else {
                        $options[$user] = 'User Id: ' . $user;
                    }
                }
            }
        } else {
            $options = array("-1" => $lng->txt("no_items"));
        }

        $si = new ilSelectInputGUI($lng->txt("user"), "userSelected");
        $si->setOptions($options);
        $si->setValue($userSelected);
        $this->form->addItem($si);

        $options = array("choose" => $lng->txt("please_choose"));
        for ($i = 0;$i < count($reports);$i++) {
            $options[$reports[$i]] = $lng->txt(strtolower($reports[$i]));
        }
        $si = new ilSelectInputGUI($lng->txt("report"), "report");
        $si->setOptions($options);
        $si->setValue($report);
        $this->form->addItem($si);
        $this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
    }
}
