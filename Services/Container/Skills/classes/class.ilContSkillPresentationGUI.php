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
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillProfileService;

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
    protected \ILIAS\Container\Skills\ContainerSkillManager $cont_skill_manager;
    protected SkillProfileService $profile_service;
    protected int $cont_member_role_id = 0;

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
        $this->cont_member_role_id = ilParticipants::getDefaultMemberRole($this->container->getRefId());

        $this->profile_service = $DIC->skills()->profile();
        $this->cont_skill_manager = $DIC->skills()->internalContainer()->manager()->getSkillManager(
            $this->container->getId(),
            $this->container->getRefId()
        );
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("showProfiles");
        $this->setPermanentLink();

        switch ($next_class) {
            case "ilpersonalskillsgui":
                $ctrl->forwardCommand($this->getPersonalSkillsGUI());
                break;

            default:
                if (in_array($cmd, ["showProfiles", "showRecords"])) {
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
        $skills = $this->cont_skill_manager->getSkillsForPresentationGUI();
        $gui->setObjectSkills($this->container->getId(), $skills);
        $gui->setObjectSkillProfiles($this->cont_member_role_id);
        return $gui;
    }

    public function showProfiles(): void
    {
        $tabs = $this->tabs;

        $tabs->activateSubTab("mem_profiles");

        if (empty($this->profile_service->getAllProfilesOfRole($this->cont_member_role_id))) {
            return;
        }

        $gui = $this->getPersonalSkillsGUI();
        $gui->listAllProfilesForGap();
    }

    public function showRecords(): void
    {
        $tabs = $this->tabs;

        $tabs->activateSubTab("mem_records");

        $gui = $this->getPersonalSkillsGUI();
        $gui->listRecordsForGap();
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
