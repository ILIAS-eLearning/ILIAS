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

/**
 * Skill presentation for container (course/group)
 *
 * @author Alex Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContSkillPresentationGUI: ilPersonalSkillsGUI
 */
class ilContSkillPresentationGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilContainerGUI $container_gui;
    protected ilContainer $container;
    protected ilObjUser $user;
    protected ilContainerSkills $container_skills;
    protected ilContainerGlobalProfiles $container_global_profiles;
    protected ilContainerLocalProfiles $container_local_profiles;
    protected ilContSkillCollector $container_skill_collector;

    public function __construct(ilContainerGUI $a_container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();

        $this->container_gui = $a_container_gui;
        /* @var $obj ilContainer */
        $obj = $this->container_gui->getObject();
        $this->container = $obj;

        $this->container_skills = new ilContainerSkills($this->container->getId());
        $this->container_global_profiles = new ilContainerGlobalProfiles($this->container->getId());
        $this->container_local_profiles = new ilContainerLocalProfiles($this->container->getId());

        $this->container_skill_collector = new ilContSkillCollector(
            $this->container_skills,
            $this->container_global_profiles,
            $this->container_local_profiles
        );
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("list");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");
        $this->setPermanentLink();

        switch ($next_class) {
            case "ilpersonalskillsgui":
                $ctrl->forwardCommand($this->getPersonalSkillsGUI());
                break;

            default:
                if ($cmd === "show") {
                    $this->$cmd();
                }
        }
    }

    protected function setPermanentLink(): void
    {
        $type = $this->container->getType();
        $ref_id = $this->container->getRefId();
        $this->tpl->setPermanentLink($type, 0, $ref_id . "_comp", "", "");
    }

    protected function getPersonalSkillsGUI(): ilPersonalSkillsGUI
    {
        $lng = $this->lng;

        $gui = new ilPersonalSkillsGUI();
        $gui->setGapAnalysisActualStatusModePerObject($this->container->getId());
        $gui->setTriggerObjectsFilter($this->getSubtreeObjectIds());
        $gui->setHistoryView(true); // NOT IMPLEMENTED YET
        $skills = $this->container_skill_collector->getSkillsForPresentationGUI();
        $gui->setObjectSkills($this->container_skills->getId(), $skills);
        $gui->setObjectSkillProfiles($this->container_global_profiles, $this->container_local_profiles);
        return $gui;
    }

    public function show(): void
    {
        $gui = $this->getPersonalSkillsGUI();
        $gui->listProfilesForGap();
    }

    protected function getSubtreeObjectIds(): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $nodes = $DIC->repositoryTree()->getSubTree(
            $DIC->repositoryTree()->getNodeData($this->container->getRefId())
        );

        $objects = [];

        foreach ($nodes as $node) {
            $objects[] = $node['obj_id'];
        }

        return $objects;
    }

    public static function isAccessible(int $ref_id): bool
    {
        global $DIC;

        $access = $DIC->access();

        $obj_id = ilObject::_lookupObjId($ref_id);
        if ($access->checkAccess('read', '', $ref_id) && ilContainer::_lookupContainerSetting(
            $obj_id,
            ilObjectServiceSettingsGUI::SKILLS,
            '0'
        )) {
            $skmg_set = new ilSetting("skmg");
            if ($skmg_set->get("enable_skmg")) {
                return true;
            }
        }
        return false;
    }
}
