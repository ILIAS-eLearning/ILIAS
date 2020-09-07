<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Group user actions (add to group)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGroup
 */
class ilGroupUserActionProvider extends ilUserActionProvider
{
    /**
     * @var array
     */
    protected static $grp_ops = array();

    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "grp";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes()
    {
        $this->lng->loadLanguageModule("grp");

        return array(
            "add_to" => $this->lng->txt("grp_add_to_group")
        );
    }

    /**
     * Get command access for user
     *
     * @param int $a_user_id
     * @return array
     */
    public static function getCommandAccess($a_user_id)
    {
        if (!isset(self::$grp_ops[$a_user_id])) {
            $ops = array();
            $o = ilUtil::_getObjectsByOperations(array("root", "crs", "cat"), "create_grp", $a_user_id, 1);
            if (count($o) > 0) {
                $ops[] = "create_grp";
            }
            $o = ilUtil::_getObjectsByOperations("grp", "manage_members", $a_user_id, 1);
            if (count($o) > 0) {
                $ops[] = "manage_members";
            }
            self::$grp_ops[$a_user_id] = $ops;
        }
        return self::$grp_ops[$a_user_id];
    }


    /**
     * Collect user actions
     *
     * @param int $a_target_user target user
     * @return ilUserActionCollection collection
     */
    public function collectActionsForTargetUser($a_target_user)
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $this->lng->loadLanguageModule("grp");

        $coll = ilUserActionCollection::getInstance();

        $commands = self::getCommandAccess($this->user_id);
        if (count($commands) == 0) {
            return $coll;
        }

        $f = new ilUserAction();
        $f->setType("add_to");
        $f->setText($this->lng->txt("grp_add_to_group"));
        $f->setHref("#");
        $ctrl->setParameterByClass("ilGroupAddToGroupActionGUI", "user_id", $a_target_user);
        $f->setData(array(
            "grp-action-add-to" => "1",
            "url" => $ctrl->getLinkTargetByClass(array("ilPersonalDesktopGUI", "ilGroupUserActionsGUI", "ilGroupAddToGroupActionGUI"), "", "", true, false)
        ));
        $coll->addAction($f);

        return $coll;
    }

    /**
     * Get js scripts
     *
     * @param string $a_action_type
     * @return array
     */
    public function getJsScripts($a_action_type)
    {
        switch ($a_action_type) {
            case "add_to":
                include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");
                return array(
                    "./Modules/Group/UserActions/js/GroupUserActions.js",
                    "./src/UI/templates/js/Modal/modal.js",
                    ilExplorerBaseGUI::getLocalExplorerJsPath(),
                    ilExplorerBaseGUI::getLocalJsTreeJsPath()
                );
                break;
        }
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getCssFiles($a_action_type)
    {
        switch ($a_action_type) {
            case "add_to":
                return array(
                    ilExplorerBaseGUI::getLocalJsTreeCssPath()
                );
                break;
        }
        return array();
    }
}
