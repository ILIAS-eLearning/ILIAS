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
        $coll = new ilUserActionCollection();

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
        global $DIC;
        $gui = $DIC->repository()->internal()->gui();
        switch ($a_action_type) {
            case "add_to":
                return array(
                    "./components/ILIAS/Group/UserActions/js/GroupUserActions.js",
                    "./components/ILIAS/UI/src/templates/js/Modal/modal.js",
                    ilExplorerBaseGUI::getLocalExplorerJsPath(),
                    ilExplorerBaseGUI::getLocalJsTreeJsPath()
                );
        }
        return array();
    }
}
