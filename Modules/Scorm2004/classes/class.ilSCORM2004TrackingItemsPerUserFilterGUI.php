<?php

declare(strict_types=1);

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
 * Class ilSCORM2004TrackingItemsPerUserFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004TrackingItemsPerUserFilterGUI extends ilPropertyFormGUI
{
    protected ilObjSCORM2004LearningModuleGUI $parent_obj;

    protected string $parent_cmd;

    public ilPropertyFormGUI $form;

    public function __construct(ilObjSCORM2004LearningModuleGUI $a_parent_obj, string $a_parent_cmd)
    {
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * @throws ilCtrlException
     */
    public function parse(string $userSelected, string $report, array $reports): void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));
        $users = ilTrQuery::getParticipantsForObject($this->parent_obj->object->getRefID());
        $privacy = ilPrivacySettings::getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        if ($users && count($users) > 0) {
            foreach ($users as $user) {
                if (ilObject::_exists((int) $user) && ilObject::_lookUpType((int) $user) === 'usr') {
                    if ($allowExportPrivacy == true) {
                        $e_user = new ilObjUser((int) $user);
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
        foreach ($reports as $value) {
            $options[$value] = $lng->txt(strtolower($value));
        }
        $si = new ilSelectInputGUI($lng->txt("report"), "report");
        $si->setOptions($options);
        $si->setValue($report);
        $this->form->addItem($si);
        $this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
    }
}
