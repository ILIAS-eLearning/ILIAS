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
 * Skill profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfile implements ilSkillUsageInfo
{
    protected int $id = 0;
    protected string $title = "";
    protected string $description = "";
    protected int $skill_tree_id = 0;
    protected string $image_id = "";
    protected int $ref_id = 0;
    /**
     * @var array{base_skill_id: int, tref_id: int, level_id: int, order_nr: int}[]
     */
    protected array $skill_level = [];

    protected ilSkillProfileLevelsDBRepository $profile_levels_repo;

    public function __construct(
        int $id,
        string $title,
        string $description,
        int $skill_tree_id,
        string $image_id = "",
        int $ref_id = 0
    ) {
        global $DIC;

        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->skill_tree_id = $skill_tree_id;
        $this->image_id = $image_id;
        $this->ref_id = $ref_id;

        $this->profile_levels_repo = $DIC->skills()->internal()->repo()->getProfileLevelsRepo();

        if ($this->getId() > 0) {
            $levels = $this->profile_levels_repo->getProfileLevels($this->getId());
            foreach ($levels as $level) {
                $this->addSkillLevel(
                    $level["base_skill_id"],
                    $level["tref_id"],
                    $level["level_id"],
                    $level["order_nr"]
                );
            }
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getImageId() : string
    {
        return $this->image_id;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }

    public function addSkillLevel(int $a_base_skill_id, int $a_tref_id, int $a_level_id, int $a_order_nr) : void
    {
        $this->skill_level[] = array(
            "base_skill_id" => $a_base_skill_id,
            "tref_id" => $a_tref_id,
            "level_id" => $a_level_id,
            "order_nr" => $a_order_nr
            );
    }

    public function removeSkillLevel(int $a_base_skill_id, int $a_tref_id, int $a_level_id, int $a_order_nr) : void
    {
        foreach ($this->skill_level as $k => $sl) {
            if ((int) $sl["base_skill_id"] == $a_base_skill_id &&
                (int) $sl["tref_id"] == $a_tref_id &&
                (int) $sl["level_id"] == $a_level_id &&
                (int) $sl["order_nr"] == $a_order_nr) {
                unset($this->skill_level[$k]);
            }
        }
    }

    /**
     * @return array{base_skill_id: int, tref_id: int, level_id: int, order_nr: int}[]
     */
    public function getSkillLevels() : array
    {
        usort($this->skill_level, static function (array $level_a, array $level_b) : int {
            return $level_a['order_nr'] <=> $level_b['order_nr'];
        });

        return $this->skill_level;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids) : array
    {
        return ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            ilSkillUsage::PROFILE,
            "skl_profile_level",
            "profile_id",
            "base_skill_id"
        );
    }
}
