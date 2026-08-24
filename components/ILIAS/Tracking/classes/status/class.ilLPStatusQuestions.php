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
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;

class ilLPStatusQuestions extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_questions';
    protected const string LNG_TEXT_INFO = 'trac_mode_questions_info';
    protected ilLanguage $lng;

    public static function _getInProgress(int $a_obj_id): array
    {
        // Exclude all users with status completed.
        return array_diff(
            ilChangeEvent::lookupUsersInProgress($a_obj_id),
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $usr_ids = [];
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        foreach ($users as $user_id) {
            // :TODO: this ought to be optimized
            $tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $user_id);
            if ($tracker->getAllQuestionsCorrect()) {
                $usr_ids[] = $user_id;
            }
        }
        return $usr_ids;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
            $tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $a_usr_id);
            if ($tracker->getAllQuestionsCorrect()) {
                $status = self::LP_STATUS_COMPLETED_NUM;
            }
        }
        return $status;
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_QUESTIONS;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
