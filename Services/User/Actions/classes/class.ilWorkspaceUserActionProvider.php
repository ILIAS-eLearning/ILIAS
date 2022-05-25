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
 * Adds link to shared resources
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceUserActionProvider extends ilUserActionProvider
{
    protected bool $wsp_activated;

    public function __construct()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        $this->wsp_activated = (!$ilSetting->get("disable_personal_workspace"));
        $lng->loadLanguageModule("wsp");
        parent::__construct();
    }

    public function getComponentId() : string
    {
        return "pwsp";
    }

    /**
     * @return array<string,string>
     */
    public function getActionTypes() : array
    {
        return array(
            "shared_res" => $this->lng->txt("wsp_shared_resources")
        );
    }

    public function collectActionsForTargetUser(int $a_target_user) : ilUserActionCollection
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $coll = ilUserActionCollection::getInstance();

        if (!$this->wsp_activated) {
            return $coll;
        }

        $f = new ilUserAction();
        $f->setType("shared_res");
        $f->setText($lng->txt("wsp_shared_resources"));
        $ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", ilObjUser::_lookupLogin($a_target_user));
        $f->setHref($ilCtrl->getLinkTargetByClass(
            array("ildashboardgui", "ilpersonalworkspacegui", "ilobjworkspacerootfoldergui"),
            "listSharedResourcesOfOtherUser"
        ));

        //$f->setData(array("test" => "you", "user" => $a_target_user));

        $coll->addAction($f);

        return $coll;
    }
}
