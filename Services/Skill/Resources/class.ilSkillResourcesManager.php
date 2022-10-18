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

/**
 * Manages skill resources
 *
 * (business logic)
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillResourcesManager
{
    protected ilSkillResources $res;
    protected int $current_target_level = 0;

    public function __construct(int $a_base_skill = 0, int $a_tref_id = 0)
    {
        $this->res = new ilSkillResources($a_base_skill, $a_tref_id);
    }

    public function isLevelTooLow(array $a_levels, array $profile_levels, array $actual_levels): bool
    {
        $too_low = true;

        foreach ($a_levels as $k => $v) {
            foreach ($profile_levels as $pl) {
                if ($pl["level_id"] == $v["id"] &&
                    $pl["base_skill_id"] == $v["skill_id"]) {
                    $too_low = true;
                    $this->current_target_level = $v["id"];
                }
            }

            if ($actual_levels[$v["skill_id"]][$this->res->getTemplateRefId()] == $v["id"]) {
                $too_low = false;
            }
        }

        return $too_low;
    }

    /**
     * @return array{level_id: int, rep_ref_id: int, trigger: int, imparting: int}[]
     */
    public function getSuggestedResources(): array
    {
        $resources = $this->res->getResources();
        $imp_resources = [];
        foreach ($resources as $level) {
            foreach ($level as $r) {
                if ($r["imparting"] &&
                    $this->current_target_level == $r["level_id"]) {
                    $imp_resources[] = $r;
                }
            }
        }

        return $imp_resources;
    }
}
