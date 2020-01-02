<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill presentatio for container (course/group)
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesContainer
 * @ilCtrl_Calls ilContSkillPresentationGUI: ilPersonalSkillsGUI
 */
class ilContSkillPresentationGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilContainerGUI
     */
    protected $container_gui;

    /**
     * @var ilContainer
     */
    protected $container;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();

        $this->container_gui = $a_container_gui;
        $this->container = $a_container_gui->object;

        include_once("./Services/Container/Skills/classes/class.ilContainerSkills.php");
        $this->container_skills = new ilContainerSkills($this->container->getId());
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("list");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            case "ilpersonalskillsgui":
                $ctrl->forwardCommand($this->getPersonalSkillsGUI());
                break;
            
            default:
                if (in_array($cmd, array("show"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Get personal skills gui
     *
     * @return ilPersonalSkillsGUI
     */
    protected function getPersonalSkillsGUI()
    {
        $lng = $this->lng;

        include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
        $gui = new ilPersonalSkillsGUI();
        $gui->setGapAnalysisActualStatusModePerObject($this->container->getId());
        $gui->setTriggerObjectsFilter($this->getSubtreeObjectIds());
        $gui->setHistoryView(true); // NOT IMPLEMENTED YET
        $skills = array_map(function ($v) {
            return array(
                "base_skill_id" => $v["skill_id"],
                "tref_id" => $v["tref_id"]
            );
        }, $this->container_skills->getSkills());
        $gui->setObjectSkills($this->container_skills->getId(), $skills);
        return $gui;
    }



    /**
     * Show
     */
    public function show()
    {
        $gui = $this->getPersonalSkillsGUI();
        $gui->listProfilesForGap();
    }

    protected function getSubtreeObjectIds()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $nodes = $DIC->repositoryTree()->getSubTree(
            $DIC->repositoryTree()->getNodeData($this->container->getRefId())
        );
        
        $objects = array();
        
        foreach ($nodes as $node) {
            $objects[] = $node['obj_id'];
        }


        return $objects;
    }
}
