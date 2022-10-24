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

namespace ILIAS\Skill\Service;

/**
 * Skill user service
 * @author famula@leifos.de
 */
class SkillUserService
{
    protected SkillInternalManagerService $manager_service;
    protected int $user_id = 0;

    public function __construct(int $user_id, SkillInternalManagerService $manager_service = null)
    {
        global $DIC;

        $this->user_id = $user_id;
        $this->manager_service = ($manager_service)
            ?: $DIC->skills()->internal()->manager();
    }

    public function writeSkillLevel(
        int $a_level_id,
        int $a_trigger_ref_id,
        int $a_tref_id = 0,
        bool $a_self_eval = false,
        string $a_unique_identifier = "",
        float $a_next_level_fulfilment = 0.0
    ): void {
        $user_id = $this->user_id;
        $this->manager_service->getUserLevelManager()->writeSkillLevel(
            $user_id,
            $a_level_id,
            $a_trigger_ref_id,
            $a_tref_id,
            $a_self_eval,
            $a_unique_identifier,
            $a_next_level_fulfilment
        );
    }

    public function getProfiles()
    {
        // repo for ilSkillProfile needed
    }
}
