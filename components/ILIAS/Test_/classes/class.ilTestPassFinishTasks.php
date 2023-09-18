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

/**
 * Class ilTestPassFinishTasks
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilTestPassFinishTasks
{
    public function __construct(
        private ilTestSession $test_session,
        private int $obj_id
    ) {
    }

    public function performFinishTasks(ilTestProcessLocker $process_locker)
    {
        $process_locker->executeTestFinishOperation(function () {
            if (!$this->test_session->isSubmitted()) {
                $this->test_session->setSubmitted();
                $this->test_session->setSubmittedTimestamp();
                $this->test_session->saveToDb();
            }

            $last_started_pass = (
                $this->test_session->getLastStartedPass() === null ? -1 : $this->test_session->getLastStartedPass()
            );

            $last_finished_pass = (
                $this->test_session->getLastFinishedPass() === null ? -1 : $this->test_session->getLastFinishedPass()
            );

            if ($last_started_pass > -1 && $last_finished_pass < $last_started_pass) {
                $this->test_session->setLastFinishedPass($this->test_session->getPass());
                $this->test_session->increaseTestPass(); // saves to db
            }
        });

        $this->updateLearningProgressAfterPassFinishedIsWritten();
    }

    protected function updateLearningProgressAfterPassFinishedIsWritten()
    {
        ilLPStatusWrapper::_updateStatus(
            $this->obj_id,
            ilObjTestAccess::_getParticipantId($this->test_session->getActiveId())
        );

        $caller = $this->getCaller();
        $lp = ilLPStatus::_lookupStatus($this->obj_id, $this->test_session->getUserId());
        $debug = "finPass={$this->test_session->getLastFinishedPass()} / Lp={$lp}";

        ilObjAssessmentFolder::_addLog(
            $this->test_session->getUserId(),
            $this->obj_id,
            "updateLearningProgressAfterPassFinishedIsWritten has been called from {$caller} ({$debug})",
            true
        );
    }

    protected function getCaller()
    {
        try {
            throw new Exception();
        } catch (Exception $e) {
            $trace = $e->getTrace();
        }

        return $trace[3]['class'];
    }
}
