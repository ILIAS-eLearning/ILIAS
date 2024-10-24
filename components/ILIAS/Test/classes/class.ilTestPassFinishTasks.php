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

use ILIAS\Test\Results\StatusOfAttempt;
use ILIAS\Test\Results\TestPassResultRepository;

/**
 * Class ilTestPassFinishTasks
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilTestPassFinishTasks
{
    public function __construct(
        private ilTestSession $test_session,
        private int $obj_id,
        private TestPassResultRepository $test_pass_result_repository
    ) {
    }

    public function performFinishTasks(ilTestProcessLocker $process_locker, StatusOfAttempt $status_of_attempt)
    {
        $process_locker->executeTestFinishOperation(function () use ($status_of_attempt) {
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

            $this->test_pass_result_repository->finalizeTestPassResult(
                $this->test_session->getActiveId(),
                $this->test_session->getPass(),
                $status_of_attempt
            );
        });

        $this->updateLearningProgressAfterPassFinishedIsWritten();
    }

    protected function updateLearningProgressAfterPassFinishedIsWritten()
    {
        ilLPStatusWrapper::_updateStatus(
            $this->obj_id,
            ilObjTestAccess::_getParticipantId($this->test_session->getActiveId())
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
