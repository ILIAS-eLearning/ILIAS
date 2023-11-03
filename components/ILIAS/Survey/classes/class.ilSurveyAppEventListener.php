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
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSurveyAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;

        $survey_skill_deletion_manager = $DIC->skills()->internalSurvey()->manager()->getSkillDeletionManager();

        switch ($a_component) {
            case "Services/Skill":
                switch ($a_event) {
                    case "deleteSkill":
                        $survey_skill_deletion_manager->removeSurveySkillsForSkill(
                            $a_parameter["node_id"],
                            $a_parameter["is_reference"]
                        );
                        break;
                }
                break;
        }
    }
}
