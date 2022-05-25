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

use ILIAS\Skill\Service\SkillTreeService;
use ILIAS\Skill\Service\SkillProfileService;

/**
 * Collector of skills for a container
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilContSkillCollector
{
    protected array $tab_skills = [];
    protected array $pres_skills = [];
    protected ilContainerSkills $container_skills;
    protected ilContainerGlobalProfiles $container_global_profiles;
    protected ilContainerLocalProfiles $container_local_profiles;
    protected ilSkillManagementSettings $skmg_settings;
    protected SkillTreeService $tree_service;
    protected SkillProfileService $profile_service;

    public function __construct(
        ilContainerSkills $a_cont_skills,
        ilContainerGlobalProfiles $a_cont_glb_profiles,
        ilContainerLocalProfiles $a_cont_lcl_profiles
    ) {
        global $DIC;

        $this->container_skills = $a_cont_skills;
        $this->container_global_profiles = $a_cont_glb_profiles;
        $this->container_local_profiles = $a_cont_lcl_profiles;
        $this->skmg_settings = new ilSkillManagementSettings();
        $this->tree_service = $DIC->skills()->tree();
        $this->profile_service = $DIC->skills()->profile();
    }

    public function getSkillsForTableGUI() : array
    {
        // Get single and profile skills WITHOUT array keys so as not to remove multiple occurrences when merging

        $s_skills = array_values($this->getSingleSkills());
        $p_skills = $this->getProfileSkills();

        $this->tab_skills = array_merge($s_skills, $p_skills);

        // order skills per virtual skill tree
        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        $this->tab_skills = $vtree->getOrderedNodeset($this->tab_skills, "base_skill_id", "tref_id");

        return $this->tab_skills;
    }

    public function getSkillsForPresentationGUI() : array
    {
        // Get single and profile skills WITH array keys so as to remove multiple occurrences when merging

        $s_skills = $this->getSingleSkills();
        $p_skills = [];

        foreach ($this->getProfileSkills() as $ps) {
            $p_skills[$ps["base_skill_id"] . "-" . $ps["tref_id"]] = [
                "base_skill_id" => $ps["base_skill_id"],
                "tref_id" => $ps["tref_id"],
                "title" => $ps["title"],
                "profile" => $ps["profile"]
            ];
        }

        $this->pres_skills = array_merge($s_skills, $p_skills);

        return $this->pres_skills;
    }

    protected function getSingleSkills() : array
    {
        $s_skills = array_map(static function (array $v) : array {
            return [
                "base_skill_id" => $v["skill_id"],
                "tref_id" => $v["tref_id"],
                "title" => ilBasicSkill::_lookupTitle($v["skill_id"], $v["tref_id"])
            ];
        }, $this->container_skills->getSkills());

        return $s_skills;
    }

    protected function getProfileSkills() : array
    {
        $p_skills = [];
        // Global skills
        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            foreach ($this->container_global_profiles->getProfiles() as $gp) {
                $profile = $this->profile_service->getById($gp["profile_id"]);
                $sklvs = $profile->getSkillLevels();
                foreach ($sklvs as $s) {
                    $p_skills[] = [
                        "base_skill_id" => $s["base_skill_id"],
                        "tref_id" => $s["tref_id"],
                        "title" => ilBasicSkill::_lookupTitle($s["base_skill_id"], $s["tref_id"]),
                        "profile" => $profile->getTitle()
                    ];
                }
            }
        }

        // Local skills
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            foreach ($this->container_local_profiles->getProfiles() as $lp) {
                $profile = $this->profile_service->getById($lp["profile_id"]);
                $sklvs = $profile->getSkillLevels();
                foreach ($sklvs as $s) {
                    $p_skills[] = [
                        "base_skill_id" => $s["base_skill_id"],
                        "tref_id" => $s["tref_id"],
                        "title" => ilBasicSkill::_lookupTitle($s["base_skill_id"], $s["tref_id"]),
                        "profile" => $profile->getTitle()
                    ];
                }
            }
        }

        return $p_skills;
    }
}
