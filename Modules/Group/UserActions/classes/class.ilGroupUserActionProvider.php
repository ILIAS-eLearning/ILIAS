<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group user actions (add to group)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ModulesGroup
 */
class ilGroupUserActionProvider extends ilUserActionProvider
{
    protected static $grp_ops = [];

    /**
     * @inheritdoc
     */
    public function getComponentId(): string
    {
        return "grp";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes(): array
    {
        $this->lng->loadLanguageModule("grp");

        return array(
            "add_to" => $this->lng->txt("grp_add_to_group")
        );
    }

    public static function getCommandAccess(int $a_user_id): array
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
     * @inheritDoc
     */
    public function collectActionsForTargetUser(int $a_target_user): ilUserActionCollection
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
            "url" => $ctrl->getLinkTargetByClass(array("ildashboardgui", "ilGroupUserActionsGUI", "ilGroupAddToGroupActionGUI"), "", "", true, false)
        ));
        $coll->addAction($f);

        return $coll;
    }

    /**
     * @inheritDoc
     */
    public function getJsScripts(string $a_action_type): array
    {
        switch ($a_action_type) {
            case "add_to":
                return array(
                    "./Modules/Group/UserActions/js/GroupUserActions.js",
                    "./src/UI/templates/js/Modal/modal.js",
                    ilExplorerBaseGUI::getLocalExplorerJsPath(),
                    ilExplorerBaseGUI::getLocalJsTreeJsPath()
                );
        }
        return array();
    }
}
