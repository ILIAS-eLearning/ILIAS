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

namespace ILIAS\Container\Skills;

use ILIAS\Skill\Service as SkillService;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerSkillManager
{
    protected int $cont_obj_id;
    protected int $cont_ref_id;
    protected ContainerSkillDBRepository $cont_skill_repo;
    protected ContainerMemberSkillDBRepository $cont_member_skill_repo;
    protected SkillService\SkillTreeService $tree_service;
    protected SkillService\SkillProfileService $profile_service;
    protected SkillService\SkillPersonalService $personal_service;
    protected ContainerSkillInternalFactoryService $factory_service;
    protected \ilSkillManagementSettings $skmg_settings;

    public function __construct(
        int $cont_obj_id,
        int $cont_ref_id,
        ContainerSkillDBRepository $cont_skill_repo = null,
        ContainerMemberSkillDBRepository $cont_member_skill_repo = null,
        SkillService\SkillTreeService $tree_service = null,
        SkillService\SkillProfileService $profile_service = null,
        SkillService\SkillPersonalService $personal_service = null,
        ContainerSkillInternalFactoryService $factory_service = null,
        \ilSkillManagementSettings $skmg_settings = null
    ) {
        global $DIC;

        $this->cont_obj_id = $cont_obj_id;
        $this->cont_ref_id = $cont_ref_id;
        $this->cont_skill_repo = ($cont_skill_repo)
            ?: $DIC->skills()->internalContainer()->repo()->getContainerSkillRepo();
        $this->cont_member_skill_repo = ($cont_member_skill_repo)
            ?: $DIC->skills()->internalContainer()->repo()->getContainerMemberSkillRepo();
        $this->tree_service = ($tree_service) ?: $DIC->skills()->tree();
        $this->profile_service = ($profile_service) ?: $DIC->skills()->profile();
        $this->personal_service = ($personal_service) ?: $DIC->skills()->personal();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internalContainer()->factory();
        $this->skmg_settings = ($skmg_settings) ?: new \ilSkillManagementSettings();
    }

    ////
    //// Container skills
    ////

    public function addSkillForContainer(int $skill_id, int $tref_id): void
    {
        $this->cont_skill_repo->add($this->cont_obj_id, $skill_id, $tref_id);
    }

    public function removeSkillFromContainer(int $skill_id, int $tref_id): void
    {
        $this->cont_skill_repo->remove($this->cont_obj_id, $skill_id, $tref_id);
    }

    /**
     * @return ContainerSkill[]
     */
    public function getSkillsForContainerOrdered(): array
    {
        $skills_as_array = [];
        foreach ($this->getSkillsForContainer() as $skill) {
            $skills_as_array[$skill->getBaseSkillId() . "-" . $skill->getTrefId()] = [
                "skill_id" => $skill->getBaseSkillId(),
                "tref_id" => $skill->getTrefId()
            ];
        }

        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        $skills_ordered = $vtree->getOrderedNodeset($skills_as_array, "skill_id", "tref_id");

        $skills_obj_ordered = [];
        foreach ($skills_ordered as $s) {
            $skills_obj_ordered[] = $this->factory_service->containerSkill()->skill(
                $this->cont_obj_id,
                $s["skill_id"],
                $s["tref_id"]
            );
        }

        return $skills_obj_ordered;
    }

    /**
     * @return ContainerSkill[]
     */
    public function getSkillsForContainer(): array
    {
        return $this->cont_skill_repo->getAll($this->cont_obj_id);
    }


    ////
    //// Container member skills
    ////

    public function addMemberSkillForContainer(int $user_id, int $skill_id, int $tref_id, int $level_id): void
    {
        $this->cont_member_skill_repo->add($this->cont_obj_id, $user_id, $skill_id, $tref_id, $level_id);
    }

    public function removeMemberSkillFromContainer(int $user_id, int $skill_id, int $tref_id): void
    {
        $this->cont_member_skill_repo->remove($this->cont_obj_id, $user_id, $skill_id, $tref_id);
    }

    public function removeAllMemberSkillsFromContainer(int $user_id): void
    {
        \ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $user_id,
            $this->cont_obj_id,
            false,
            (string) $this->cont_obj_id
        );

        $this->cont_member_skill_repo->removeAll($this->cont_obj_id, $user_id);
    }

    public function publishMemberSkills(int $user_id): bool
    {
        $changed = \ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $user_id,
            $this->cont_obj_id,
            false,
            (string) $this->cont_obj_id
        );

        foreach ($this->getMemberSkillsForContainer($user_id) as $l) {
            $changed = true;
            \ilBasicSkill::writeUserSkillLevelStatus(
                $l->getLevelId(),
                $user_id,
                $this->cont_ref_id,
                $l->getTrefId(),
                \ilBasicSkill::ACHIEVED,
                false,
                false,
                (string) $this->cont_obj_id
            );

            if ($l->getTrefId() > 0) {
                $this->personal_service->addPersonalSkill($user_id, $l->getTrefId());
            } else {
                $this->personal_service->addPersonalSkill($user_id, $l->getBaseSkillId());
            }
        }

        //write profile completion entries if fulfilment status has changed
        $this->profile_service->writeCompletionEntryForAllProfiles($user_id);

        $this->cont_member_skill_repo->publish($this->cont_obj_id, $user_id);

        return $changed;
    }

    public function getPublished(int $user_id): bool
    {
        return $this->cont_member_skill_repo->getPublished($this->cont_obj_id, $user_id);
    }

    /**
     * @return ContainerMemberSkill[]
     */
    public function getMemberSkillLevelsForContainerOrdered(int $user_id): array
    {
        $skill_levels = array_map(static function (ContainerMemberSkill $a): array {
            return ["level_id" => $a->getLevelId(), "skill_id" => $a->getBaseSkillId(), "tref_id" => $a->getTrefId(),
                "published" => $a->getPublished()];
        }, $this->getMemberSkillsForContainer($user_id));

        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        $skill_levels_ordered = $vtree->getOrderedNodeset($skill_levels, "skill_id", "tref_id");

        $skill_levels_obj_ordered = [];
        foreach ($skill_levels_ordered as $s) {
            $skill_levels_obj_ordered[] = $this->factory_service->containerSkill()->memberSkill(
                $this->cont_obj_id,
                $user_id,
                $s["skill_id"],
                $s["tref_id"],
                $s["level_id"],
                $s["published"]
            );
        }

        return $skill_levels_obj_ordered;
    }

    /**
     * @return ContainerMemberSkill[]
     */
    public function getMemberSkillsForContainer(int $user_id): array
    {
        return $this->cont_member_skill_repo->getAll($this->cont_obj_id, $user_id);
    }

    public function getMemberSkillLevel(int $user_id, int $skill_id, int $tref_id): ?int
    {
        return $this->cont_member_skill_repo->getLevel($this->cont_obj_id, $user_id, $skill_id, $tref_id);
    }


    ////
    //// Container skill collecting
    ////

    /**
     * @return ContainerSkill[]
     */
    public function getSkillsForTableGUI(): array
    {
        // Get single and profile skills and DO NOT remove multiple occurrences when merging

        $skills = array_merge($this->getSingleSkills(), $this->getProfileSkills());

        $skills_as_array = [];
        foreach ($skills as $s) {
            $skills_as_array[] = [
                "base_skill_id" => $s->getBaseSkillId(),
                "tref_id" => $s->getTrefId(),
                "title" => $s->getTitle(),
                "profile_title" => ($s->getProfile()) ? $s->getProfile()->getTitle() : null
            ];
        }

        // order skills per virtual skill tree
        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        $skills = $vtree->getOrderedNodeset($skills_as_array, "base_skill_id", "tref_id");

        return $skills;
    }

    /**
     * @return ContainerSkill[]
     */
    public function getSkillsForPresentationGUI(): array
    {
        // Get single and profile skills and DO remove multiple occurrences when merging

        $s_skills = [];
        foreach ($this->getSingleSkills() as $s) {
            $s_skills[$s->getBaseSkillId() . "-" . $s->getTrefId()] = $s;
        }

        $p_skills = [];
        foreach ($this->getProfileSkills() as $ps) {
            $p_skills[$ps->getBaseSkillId() . "-" . $ps->getTrefId()] = $ps;
        }

        $skills = array_merge($s_skills, $p_skills);

        return $skills;
    }

    /**
     * @return ContainerSkill[]
     */
    protected function getSingleSkills(): array
    {
        $s_skills = array_map(function (ContainerSkill $v): ContainerSkill {
            return $this->factory_service->containerSkill()->skill(
                $this->cont_obj_id,
                $v->getBaseSkillId(),
                $v->getTrefId(),
                \ilBasicSkill::_lookupTitle($v->getBaseSkillId(), $v->getTrefId())
            );
        }, $this->getSkillsForContainer());

        return $s_skills;
    }

    /**
     * @return ContainerSkill[]
     */
    protected function getProfileSkills(): array
    {
        $cont_member_role_id = \ilParticipants::getDefaultMemberRole($this->cont_ref_id);
        $p_skills = [];
        // Global skills
        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            foreach ($this->profile_service->getGlobalProfilesOfRole($cont_member_role_id) as $gp) {
                $sklvs = $this->profile_service->getSkillLevels($gp->getId());
                foreach ($sklvs as $s) {
                    $p_skills[] = $this->factory_service->containerSkill()->skill(
                        $this->cont_obj_id,
                        $s->getBaseSkillId(),
                        $s->getTrefId(),
                        \ilBasicSkill::_lookupTitle($s->getBaseSkillId(), $s->getTrefId()),
                        $gp
                    );
                }
            }
        }

        // Local skills
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            foreach ($this->profile_service->getLocalProfilesOfRole($cont_member_role_id) as $lp) {
                $sklvs = $this->profile_service->getSkillLevels($lp->getId());
                foreach ($sklvs as $s) {
                    $p_skills[] = $this->factory_service->containerSkill()->skill(
                        $this->cont_obj_id,
                        $s->getBaseSkillId(),
                        $s->getTrefId(),
                        \ilBasicSkill::_lookupTitle($s->getBaseSkillId(), $s->getTrefId()),
                        $lp
                    );
                }
            }
        }

        return $p_skills;
    }
}
