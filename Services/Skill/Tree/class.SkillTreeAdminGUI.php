<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

// only after ilCtrl is able to handle namespaces
//namespace ILIAS\Skill\Tree;

use \ILIAS\Skill\Tree;
use \ILIAS\Skill\Service\SkillInternalManagerService;
use \ILIAS\UI\Component\Input\Container\Form;
use \Psr\Http\Message\RequestInterface;

/**
 * Skill tree administration
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls SkillTreeAdminGUI: ilObjSkillTreeGUI
 */
class SkillTreeAdminGUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $main_tpl;

    /**
     * @var \ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var SkillInternalManagerService
     */
    protected $skill_manager;

    /**
     * @var Tree\SkillTreeManager
     */
    protected $skill_tree_manager;

    /**
     * Constructor
     */
    public function __construct(SkillInternalManagerService $skill_manager)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();

        $this->requested_ref_id = (int) ($_GET["ref_id"] ?? 0);

        $this->skill_manager  = $skill_manager;
        $this->skill_tree_manager = $skill_manager->getTreeManager();
    }

    /**
     * Execute command
     */
    function executeCommand() : void
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

    /**
     * List trees
     */
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

        $toolbar->addComponent($add_tree_button);

        $tab = new Tree\SkillTreeTableGUI($this, "listTrees", $this->skill_tree_manager);
        $mtpl->setContent($tab->getHTML());
    }


}