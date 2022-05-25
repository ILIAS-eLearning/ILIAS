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
 ********************************************************************
 */

// only after ilCtrl is able to handle namespaces
//namespace ILIAS\Skill\Tree;

use ILIAS\DI\UIServices;
use ILIAS\Skill\Tree;
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\UI\Component\Input\Container\Form;
use Psr\Http\Message\RequestInterface;

/**
 * Skill tree administration
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls SkillTreeAdminGUI: ilObjSkillTreeGUI
 */
class SkillTreeAdminGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilToolbarGUI $toolbar;
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected RequestInterface $request;
    protected int $requested_ref_id = 0;
    protected ilTabsGUI $tabs;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected SkillInternalManagerService $skill_manager;
    protected Tree\SkillTreeManager $skill_tree_manager;
    protected SkillManagementAccess $skill_management_access_manager;

    public function __construct(SkillInternalManagerService $skill_manager)
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $this->requested_ref_id = $this->admin_gui_request->getRefId();

        $this->skill_manager = $skill_manager;
        $this->skill_tree_manager = $this->skill_manager->getTreeManager();
        $this->skill_management_access_manager = $this->skill_manager->getManagementAccessManager($this->requested_ref_id);
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listTrees");

        switch ($next_class) {

            case "ilobjskilltreegui":
                $this->tabs->clearTargets();
                $gui = new ilObjSkillTreeGUI([], $this->requested_ref_id, true, false);
                $gui->init($this->skill_manager);
                $ctrl->forwardCommand($gui);
                break;

            default:
                if (in_array($cmd, ["listTrees", "createSkillTree", "updateTree", "createTree"])) {
                    $this->$cmd();
                }
        }
    }

    protected function listTrees() : void
    {
        $mtpl = $this->main_tpl;
        $toolbar = $this->toolbar;
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $add_tree_button = $ui->factory()->button()->standard(
            $lng->txt("skmg_add_skill_tree"),
            $ctrl->getLinkTargetByClass("ilobjskilltreegui", "create")
        );

        if ($this->skill_management_access_manager->hasCreateTreePermission()) {
            $toolbar->addComponent($add_tree_button);
        }

        $tab = new Tree\SkillTreeTableGUI($this, "listTrees", $this->skill_manager);
        $mtpl->setContent($tab->getHTML());
    }
}
