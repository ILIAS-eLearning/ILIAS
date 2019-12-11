<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Adds link to shared resources
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilWorkspaceUserActionProvider extends ilUserActionProvider
{
    protected $wsp_activated;

    /**
     * Construct
     *
     * @param
     * @return
     */
    public function __construct()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        $this->wsp_activated = (!$ilSetting->get("disable_personal_workspace"));
        $lng->loadLanguageModule("wsp");
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "pwsp";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes()
    {
        return array(
            "shared_res" => $this->lng->txt("wsp_shared_resources")
        );
    }

    /**
     * Collect all actions
     *
     * @param int $a_target_user target user
     * @return ilUserActionCollection collection
     */
    public function collectActionsForTargetUser($a_target_user)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $coll = ilUserActionCollection::getInstance();
        include_once("./Services/User/Actions/classes/class.ilUserAction.php");

        if (!$this->wsp_activated) {
            return $coll;
        }

        $f = new ilUserAction();
        $f->setType("shared_res");
        $f->setText($lng->txt("wsp_shared_resources"));
        $ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", ilObjUser::_lookupLogin($a_target_user));
        $f->setHref($ilCtrl->getLinkTargetByClass(
            array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjworkspacerootfoldergui"),
            "listSharedResourcesOfOtherUser"
        ));

        //$f->setData(array("test" => "you", "user" => $a_target_user));

        $coll->addAction($f);

        return $coll;
    }
}
