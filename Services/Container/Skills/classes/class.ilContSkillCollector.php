<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collector of skills for a container
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilContSkillCollector
{
    /**
     * @var array
     */
    protected $tab_skills = array();

    /**
     * @var array
     */
    protected $pres_skills = array();

    /**
     * @var ilContainerSkills
     */
    protected $cont_skills;

    /**
     * @var ilContainerProfiles
     */
    protected $cont_profiles;

    /**
     * Constructor
     *
     * @param ilContainerSkills   $a_cont_skills
     * @param ilContainerProfiles $a_cont_profiles
     */
    public function __construct(ilContainerSkills $a_cont_skills, ilContainerProfiles $a_cont_profiles)
    {
        $this->container_skills = $a_cont_skills;
        $this->container_profiles = $a_cont_profiles;
    }

    /**
     * @return array
     */
    public function getSkillsForTableGUI() : array
    {
        // Get single and profile skills WITHOUT array keys so as not to remove multiple occurrences when merging

        $s_skills = array_values($this->getSingleSkills());
        $p_skills = $this->getProfileSkills();

        $this->tab_skills = array_merge($s_skills, $p_skills);

        // order skills per virtual skill tree
        $vtree = new ilVirtualSkillTree();
        $this->tab_skills = $vtree->getOrderedNodeset($this->tab_skills, "base_skill_id", "tref_id");

        return $this->tab_skills;
    }

    /**
     * Get all skills for presentation gui
     *
     * @return array
     */
    public function getSkillsForPresentationGUI() : array
    {
        // Get single and profile skills WITH array keys so as to remove multiple occurrences when merging

        $s_skills = $this->getSingleSkills();
        $p_skills = array();

        foreach ($this->getProfileSkills() as $ps) {
            $p_skills[$ps["base_skill_id"] . "-" . $ps["tref_id"]] = array(
                "base_skill_id" => $ps["base_skill_id"],
                "tref_id" => $ps["tref_id"],
                "title" => $ps["title"],
                "profile" => $ps["profile"]
            );
        }

        $this->pres_skills = array_merge($s_skills, $p_skills);

        return $this->pres_skills;
    }

    /**
     * Get single skills of container
     *
     * @return array
     */
    protected function getSingleSkills() : array
    {
        $s_skills = array_map(function ($v) {
            return array(
                "base_skill_id" => $v["skill_id"],
                "tref_id" => $v["tref_id"],
                "title" => ilBasicSkill::_lookupTitle($v["skill_id"], $v["tref_id"])
            );
        }, $this->container_skills->getSkills());

        return $s_skills;
    }

    /**
     * Get profile skills of container
     *
     * @return array
     */
    protected function getProfileSkills() : array
    {
        $p_skills = array();
        foreach ($this->container_profiles->getProfiles() as $p) {
            $profile = new ilSkillProfile($p["profile_id"]);
            $sklvs = $profile->getSkillLevels();
            foreach ($sklvs as $s) {
                $p_skills[] = array(
                    "base_skill_id" => $s["base_skill_id"],
                    "tref_id" => $s["tref_id"],
                    "title" => ilBasicSkill::_lookupTitle($s["base_skill_id"], $s["tref_id"]),
                    "profile" => $profile->getTitle()
                );
            }
        }

        return $p_skills;
    }
}
