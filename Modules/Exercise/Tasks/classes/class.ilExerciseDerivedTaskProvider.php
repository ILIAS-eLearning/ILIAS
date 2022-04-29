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
 
/**
 * Exercise derived task provider
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseDerivedTaskProvider implements ilDerivedTaskProvider
{
    protected ilTaskService $task_service;
    protected ilExerciseDerivedTaskAction $task_action;
    protected ilAccess $access;
    protected ilLanguage $lng;

    public function __construct(
        ilTaskService $task_service,
        ilAccess $access,
        ilLanguage $lng,
        ilExerciseDerivedTaskAction $derived_task_action
    ) {
        $this->access = $access;
        $this->task_service = $task_service;
        $this->task_action = $derived_task_action;
        $this->lng = $lng;

        $this->lng->loadLanguageModule("exc");
    }

    public function isActive() : bool
    {
        return true;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @return \ilDerivedTask[]
     */
    public function getTasks(int $user_id) : array
    {
        $lng = $this->lng;

        $tasks = [];

        // open assignments
        foreach ($this->task_action->getOpenAssignmentsOfUser($user_id) as $ass) {
            $ref_id = $this->getFirstRefIdWithPermission("read", $ass->getExerciseId(), $user_id);
            if ($ref_id == 0) {
                continue;
            }
            $state = ilExcAssMemberState::getInstanceByIds($ass->getId(), $user_id);
            $title = str_replace("%1", $ass->getTitle(), $lng->txt("exc_task_submission"));
            $tasks[] = $this->task_service->derived()->factory()->task(
                $title,
                $ref_id,
                $state->getOfficialDeadline(),
                (int) $state->getGeneralStart()
            );
        }

        // open peer feedbacks
        foreach ($this->task_action->getOpenPeerReviewsOfUser($user_id) as $ass) {
            $ref_id = $this->getFirstRefIdWithPermission("read", $ass->getExerciseId(), $user_id);
            if ($ref_id == 0) {
                continue;
            }
            $state = ilExcAssMemberState::getInstanceByIds($ass->getId(), $user_id);
            $title = str_replace("%1", $ass->getTitle(), $lng->txt("exc_task_peer_feedback"));
            $tasks[] = $this->task_service->derived()->factory()->task(
                $title,
                $ref_id,
                $state->getPeerReviewDeadline(),
                0
            );
        }

        // open gradings
        foreach ($this->task_action->getOpenGradingsOfUser($user_id) as $ass) {
            $ref_id = $this->getFirstRefIdWithPermission("write", $ass->getExerciseId(), $user_id);
            if ($ref_id == 0) {
                continue;
            }
            $title = str_replace("%1", $ass->getTitle(), $lng->txt("exc_task_grading"));
            $tasks[] = $this->task_service->derived()->factory()->task($title, $ref_id, 0, 0);
        }

        return $tasks;
    }


    // Get first ref id for an object id with permission
    protected function getFirstRefIdWithPermission(
        string $perm,
        int $obj_id,
        int $user_id
    ) : int {
        $access = $this->access;

        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($access->checkAccessOfUser($user_id, $perm, "", $ref_id)) {
                return $ref_id;
            }
        }
        return 0;
    }
}
