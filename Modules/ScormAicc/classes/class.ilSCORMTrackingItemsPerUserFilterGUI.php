<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilSCORMTrackingItemsPerUserFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerUserFilterGUI extends ilPropertyFormGUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function parse($userSelected, $report, $reports) : void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));
        $users = ilTrQuery::getParticipantsForObject($this->parent_obj->object->ref_id);
        $privacy = ilPrivacySettings::getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        if ($users && count($users) > 0) {
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
