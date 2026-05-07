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

namespace ILIAS\Test\ExportImport\Import;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Test\ExportImport\Envelopes\AdditionalWorkingTime;
use ILIAS\Test\ExportImport\Envelopes\ManualFeedback;
use ILIAS\Test\ExportImport\Envelopes\QuestionResult;
use ILIAS\Test\ExportImport\Envelopes\Solution;
use ILIAS\Test\ExportImport\Envelopes\WorkingTime;
use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\Test\Results\Data\ParticipantResult;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ilTestSequence;
use Psr\Log\LoggerInterface;

/**
 * Imports test results and their related data from a list of normalized data.
 */
class TestResultsImporter
{
    public function __construct(
        private readonly ilDBInterface $database,
        private readonly LoggerInterface $log,
    ) {
    }

    /**
     * Import test results from a list of normalized data. It will import the test sequences, solutions and evaluation
     * results, as well as the working times and manual feedback.
     */
    public function import(array $list, Transformations $tt): void
    {
        foreach ($list as $set) {
            foreach ($set as $name => $data) {
                match($name) {
                    'sequences' => $this->importTestSequences($data, $tt),
                    'solutions' => $this->importSolutions($data, $tt),
                    'results' => $this->importQuestionResults($data, $tt),
                    'attempts' => $this->importAttemptResults($data, $tt),
                    'test_result' => $this->importTestResult($data, $tt),
                    'working_times' => $this->importWorkingTimes($data, $tt),
                    'manual_feedback' => $this->importManualFeedback($data, $tt),
                    'manual_scoring' => $this->importManualScoring($data, $tt),
                    default => $this->log->warning("Invalid result type: {$name}"),
                };
            }
        }
    }

    public function importTestSequences(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID and QuestionIDs will be replaced by the mapping pipe
            $sequence = $tt->denormalize($normalized, ilTestSequence::class);
            $sequence->saveToDb();
            $this->log->debug("Stored test sequence in database: {$sequence->getActiveId()} (Active ID), {$sequence->getPass()} (Pass)");
        }
    }

    public function importSolutions(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID and QuestionID will be replaced by the mapping pipe
            $solution = $tt->denormalize($normalized, Solution::class);

            $next_id = $this->database->nextId('tst_solutions');
            $this->database->insert(
                'tst_solutions',
                [
                    'solution_id' => [ilDBConstants::T_INTEGER, $next_id],
                    'active_fi' => [ilDBConstants::T_INTEGER, $solution->active_id->getId()],
                    'question_fi' => [ilDBConstants::T_INTEGER, $solution->question_id->getId()],
                    'pass' => [ilDBConstants::T_INTEGER, $solution->attempt],
                    'value1' => [ilDBConstants::T_TEXT, $solution->value1 !== null ? (string) $solution->value1 : null],
                    'value2' => [ilDBConstants::T_TEXT, $solution->value2],
                    'points' => [ilDBConstants::T_FLOAT, $solution->points],
                    'step' => [ilDBConstants::T_INTEGER, $solution->step],
                    'authorized' => [ilDBConstants::T_INTEGER, $solution->authorized ? 1 : 0],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                ]
            );
            $this->log->debug("Stored solution in database: {$next_id}");
        }
    }

    public function importQuestionResults(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID and QuestionID will be replaced by the mapping pipe
            $result = $tt->denormalize($normalized, QuestionResult::class);

            $next_id = $this->database->nextId('tst_test_result');
            $this->database->insert(
                'tst_test_result',
                [
                    'test_result_id' => [ilDBConstants::T_INTEGER, $next_id],
                    'active_fi' => [ilDBConstants::T_INTEGER, $result->active_id->getId()],
                    'question_fi' => [ilDBConstants::T_INTEGER, $result->question_id->getId()],
                    'pass' => [ilDBConstants::T_INTEGER, $result->attempt],
                    'points' => [ilDBConstants::T_FLOAT, $result->points],
                    'manual' => [ilDBConstants::T_INTEGER, $result->manual ? 1 : 0],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                    'answered' => [ilDBConstants::T_INTEGER, $result->answered ? 1 : 0],
                    'step' => [ilDBConstants::T_INTEGER, $result->step],
                ]
            );
            $this->log->debug("Stored question result in database: {$next_id}");
        }
    }
    public function importAttemptResults(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID will be replaced by the mapping pipe
            $attempt = $tt->denormalize($normalized, AttemptResult::class);

            $this->database->insert(
                'tst_pass_result',
                [
                    'active_fi' => [ilDBConstants::T_INTEGER, $attempt->getActiveId()],
                    'pass' => [ilDBConstants::T_INTEGER, $attempt->getAttempt()],
                    'maxpoints' => [ilDBConstants::T_FLOAT, $attempt->getMaxPoints()],
                    'points' => [ilDBConstants::T_FLOAT, $attempt->getReachedPoints()],
                    'questioncount' => [ilDBConstants::T_INTEGER, $attempt->getQuestionCount()],
                    'answeredquestions' => [ilDBConstants::T_INTEGER, $attempt->getAnsweredQuestions()],
                    'workingtime' => [ilDBConstants::T_INTEGER, $attempt->getWorkingTime()],
                    'exam_id' => [ilDBConstants::T_TEXT, $attempt->getExamId()],
                    'finalized_by' => [ilDBConstants::T_TEXT, $attempt->getFinalizedBy()],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                ]
            );
            $this->log->debug("Stored attempt result in database: {$attempt->getActiveId()} (Active ID), {$attempt->getAttempt()} (Pass)");
        }
    }

    public function importTestResult(?array $normalized, Transformations $tt): void
    {
        if ($normalized === null) {
            $this->log->warning("Missing test result, skipping");
            return;
        }

        // ActiveID will be replaced by the mapping pipe
        $result = $tt->denormalize($normalized, ParticipantResult::class);

        $this->database->insert(
            'tst_result_cache',
            [
                'active_fi' => [ilDBConstants::T_INTEGER, $result->getActiveId()],
                'pass' => [ilDBConstants::T_INTEGER, $result->getAttempt()],
                'max_points' => [ilDBConstants::T_FLOAT, $result->getMaxPoints()],
                'reached_points' => [ilDBConstants::T_FLOAT, $result->getReachedPoints()],
                'mark_short' => [ilDBConstants::T_TEXT, $result->getMark()->getShortName()],
                'mark_official' => [ilDBConstants::T_TEXT, $result->getMark()->getOfficialName()],
                'passed' => [ilDBConstants::T_INTEGER, $result->isPassed() ? 1 : 0],
                'failed' => [ilDBConstants::T_INTEGER, $result->isFailed() ? 1 : 0],
                'tstamp' => [ilDBConstants::T_INTEGER, time()],
            ]
        );
        $this->log->debug("Stored test result in database: {$result->getActiveId()} (Active ID), {$result->getAttempt()} (Pass)");
    }

    public function importWorkingTimes(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID will be replaced by the mapping pipe
            $working_time = $tt->denormalize($normalized, WorkingTime::class);

            $next_id = $this->database->nextId('tst_times');
            $this->database->insert(
                'tst_times',
                [
                    'times_id' => [ilDBConstants::T_INTEGER, $next_id],
                    'active_fi' => [ilDBConstants::T_INTEGER, $working_time->active_id->getId()],
                    'pass' => [ilDBConstants::T_INTEGER, $working_time->attempt],
                    'started' => [ilDBConstants::T_TIMESTAMP, $working_time->started],
                    'finished' => [ilDBConstants::T_TIMESTAMP, $working_time->finished],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                ]
            );
            $this->log->debug("Stored working time in database: {$next_id}");
        }
    }

    public function importManualFeedback(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // ActiveID, QuestionID and UserID will be replaced by the mapping pipe
            $manual_feedback = $tt->denormalize($normalized, ManualFeedback::class);

            $next_id = $this->database->nextId('tst_manual_fb');
            $this->database->insert(
                'tst_manual_fb',
                [
                    'manual_feedback_id' => [ilDBConstants::T_INTEGER, $next_id],
                    'active_fi' => [ilDBConstants::T_INTEGER, $manual_feedback->active_id->getId()],
                    'question_fi' => [ilDBConstants::T_INTEGER, $manual_feedback->question_id->getId()],
                    'pass' => [ilDBConstants::T_INTEGER, $manual_feedback->attempt],
                    'feedback' => [ilDBConstants::T_TEXT, $manual_feedback->feedback],
                    'finalized_evaluation' => [ilDBConstants::T_INTEGER, $manual_feedback->finalized_evaluation ? 1 : 0],
                    'finalized_timestamp' => [ilDBConstants::T_INTEGER, $manual_feedback->finalized_timestamp],
                    'finalized_by_usr_id' => [ilDBConstants::T_INTEGER, $manual_feedback->finalized_by->getId()],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                ]
            );
            $this->log->debug("Stored manual feedback in database: {$next_id}");
        }
    }

    public function importManualScoring(array $normalized, Transformations $tt): void
    {
        // ActiveID will be replaced by the mapping pipe
        $active_id = $tt->denormalize($normalized['active_id'], Id::class)->getId();

        new TestManScoringDoneHelper()->setDone($active_id, $tt->bool($normalized['done']));
        $this->log->debug("Stored manual scoring in database: {$active_id} (Active ID)");
    }

    public function importAdditionalWorkingTimes(array $list, Transformations $tt): void
    {
        foreach ($list as $normalized) {
            // UserID and TestID will be replaced by the mapping pipe
            $time = $tt->denormalize($normalized, AdditionalWorkingTime::class);

            $this->database->insert(
                'tst_addtime',
                [
                    'additionaltime' => [ilDBConstants::T_INTEGER, $time->time],
                    'user_fi' => [ilDBConstants::T_INTEGER, $time->user_id->getId()],
                    'test_fi' => [ilDBConstants::T_INTEGER, $time->test_id->getId()],
                    'tstamp' => [ilDBConstants::T_TIMESTAMP, $time->timestamp],
                ]
            );
            $this->log->debug("Stored additional working time in database: {$time->user_id->getId()} (User ID)");
        }
    }
}
