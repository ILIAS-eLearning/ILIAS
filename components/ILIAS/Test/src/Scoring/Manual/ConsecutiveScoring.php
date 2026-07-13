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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\Language\Language;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\TestManScoringDoneHelper;

class ConsecutiveScoring
{
    public function __construct(
        private readonly Positions $positions,
        private readonly \ilObjTest $object,
        private readonly \ilTestShuffler $shuffler,
        private readonly TestLogger $logger,
        private TestScoring $scorer,
        private TestManScoringDoneHelper $scoring_done_helper,
        private \ilObjUser $current_user,
        private readonly \ilTestAccess $test_access,
        private readonly ParticipantRepository $participant_repository,
        private readonly Language $lng,
    ) {
    }

    public function getPositions(): Positions
    {
        return $this->positions;
    }

    public function getAttemptUsedForEvaluation(int $usr_active_id): int
    {
        return $this->positions->getAllAttempts()[$usr_active_id];
    }

    private function getQuestionGUI(int $qid): \assQuestionGUI
    {
        return $this->object->createQuestionGUI("", $qid);
    }

    public function getQuestionObject(int $qid): \assQuestion
    {
        return $this->getQuestionGUI($qid)->getObject();
    }

    public function getParticipantNames(): array
    {
        $participants = [];
        foreach ($this->positions->getAllAttempts() as $usr_active_id => $attempt) {
            $participants[$usr_active_id] = $this->getUserFullName(
                $usr_active_id,
                (string) $attempt
            );
        };
        return $participants;
    }

    public function getQuestionTitles(\ilLanguage $lng): array
    {
        $question_titles = [];
        foreach ($this->positions->getAllQuestionProperties() as $qid => $qprop) {
            $question_titles[$qid] = sprintf(
                '%s (%s)',
                $qprop->getTitle(),
                $qprop->getTypeName($lng)
            );
        }
        return $question_titles;
    }

    public function getUserFullName(
        int $usr_active_id,
        string $attempt
    ): string {
        if ($this->object->getAnonymity()
            || !$this->test_access->checkScoreParticipantsAccess()
        ) {
            return \ilObjTest::buildExamId($usr_active_id, $attempt, $this->object->getId());
        }

        $participant = $this->participant_repository->getParticipantByActiveId($this->object->getTestId(), $usr_active_id);
        $importname = $participant->getImportname();
        $user_id = $participant->getUserId();
        if ($user_id === ANONYMOUS_USER_ID && $importname !== null && $importname !== '') {
            return $participant->getDisplayName($this->lng);
        }

        $user_id = (string) $user_id;
        $user = $this->object->getUserData([$user_id])[$user_id];
        return sprintf(
            "%s %s [%s]",
            $user["firstname"],
            $user["lastname"],
            $user["login"]
        );
    }

    public function getUserId(
        int $usr_active_id,
        string $attempt,
    ): string {
        if ($this->object->getAnonymity()
            || !$this->test_access->checkScoreParticipantsAccess()
        ) {
            return \ilObjTest::buildExamId($usr_active_id, $attempt, $this->object->getId());
        }
        return (string) $this->object->_getUserIdFromActiveId($usr_active_id);
    }

    public function getSingleManualFeedback(int $qid, int $usr_active_id, int $attempt_id): array
    {
        $fb = \ilObjTest::getSingleManualFeedback($usr_active_id, $qid, $attempt_id);
        if (array_key_exists("finalized_tstamp", $fb)) {
            $fb["finalized_time"] = $this->current_user->getDateTimeFormat()->applyTo(
                \DateTimeImmutable::createFromFormat('U', (string) $fb["finalized_tstamp"])
            );
        }
        return $fb;
    }

    public function getUserQuestionGUI(int $qid, int $usr_active_id, int $attempt_id): \assQuestionGUI
    {
        $question_gui = $this->getQuestionGUI($qid);
        $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $usr_active_id, $attempt_id);
        $question = $question_gui->getObject();
        $question->setShuffler($shuffle_trafo);
        $question_gui->setObject($question);
        return $question_gui;
    }

    /**
     * @return array<int, int[]>, uid => [qids]
     */
    public function getAnsweredQuestionIds(): array
    {
        $answered = [];
        foreach ($this->positions->getAllAttempts() as $usr_active_id => $attempt_id) {
            $answered[$usr_active_id] = [];

            $user_results = $this->object->getTestResult(
                $usr_active_id,
                $attempt_id,
                false,
                true,
                true
            );

            foreach ($user_results as $idx => $qresult) {
                if (!is_numeric($idx)) {
                    continue;
                }
                if ((bool) $qresult['answered']) {
                    $answered[$usr_active_id][] = (int) $qresult['qid'];
                }
            }
        }
        return $answered;
    }

    /**
     * @return array<int, int[]>, uid => [qids]
     */
    public function getFinalizedFeedbackIds(): array
    {
        $finalized = [];
        $usr_active_ids = array_keys($this->positions->getAllAttempts());
        foreach (array_keys($this->positions->getAllQuestionProperties()) as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);
            foreach ($usr_active_ids as $uid) {
                if (! array_key_exists($uid, $finalized)) {
                    $finalized[$uid] = [];
                }
                $attempt_id = $this->getAttemptUsedForEvaluation($uid);
                if ((bool) ($feedback[$uid][$attempt_id][$qid]['finalized_evaluation'] ?? false)) {
                    $finalized[$uid][] = $qid;
                }
            }
        }
        return $finalized;
    }

    /**
     * @return int[]>, question ids
     */
    public function getQidsFinalizedBy(array $finalizing_usr_ids): array
    {
        $finalized = [];
        $usr_active_ids = array_keys($this->positions->getAllAttempts());
        foreach (array_keys($this->positions->getAllQuestionProperties()) as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);
            foreach ($usr_active_ids as $uid) {
                $attempt_id = $this->getAttemptUsedForEvaluation($uid);
                $scorer = $feedback[$uid][$attempt_id][$qid]['finalized_by_usr_id'] ?? 0;

                if (in_array($scorer, $finalizing_usr_ids)) {
                    $finalized[] = $qid;
                }
            }
        }
        return array_unique($finalized);
    }

    /**
     * @return array <int, string>
     */
    public function getAllFinalizingUserNames(): array
    {
        $finalized_by = [];
        $attempts = $this->positions->getAllAttempts();
        $question_ids = array_keys($this->positions->getAllQuestionProperties());

        foreach ($question_ids as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);

            foreach ($attempts as $uid => $attempt_id) {
                if ($feedback[$uid][$attempt_id][$qid]['finalized_by_usr_id'] ?? false) {
                    $scorer_id = $feedback[$uid][$attempt_id][$qid]['finalized_by_usr_id'];
                    $finalized_by[$scorer_id] = $scorer_id;
                }
            }
        }
        return array_map(
            function ($scorer_id) {
                $ud = current(\ilObjUser::_getUserData([$scorer_id]));
                return $ud['firstname'] . ' ' . $ud['lastname'];
            },
            $finalized_by
        );
    }

    public function store(
        int $qid,
        int $usr_active_id,
        int $attempt_id,
        float $score,
        bool $final,
        string $feedback,
        float $max_points
    ) {
        $feedback = \ilUtil::stripSlashes(
            $feedback,
            false,
            \ilRTESettings::_getUsedHTMLTagsAsString('assessment')
        );

        // fix #35543: save manual points only if they differ from the existing points
        // this prevents a question being set to "answered" if only feedback is entered
        $previously_reached_points = $this->getQuestionObject($qid)
            ->getReachedPoints($usr_active_id, $attempt_id);
        if ($score !== $previously_reached_points) {
            \assQuestion::_setReachedPoints(
                $usr_active_id,
                $qid,
                $score,
                $max_points,
                $attempt_id,
                true
            );
        }

        $this->object->saveManualFeedback(
            $usr_active_id,
            $qid,
            $attempt_id,
            $feedback,
            $final
        );

        $this->scorer->setPreserveManualScores(true);
        $this->scorer->recalculateSolution($usr_active_id, $attempt_id);

        \ilLPStatusWrapper::_updateStatus(
            $this->object->getId(),
            \ilObjTestAccess::_getParticipantId($usr_active_id)
        );

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logScoringInteraction(
                $this->logger->getInteractionFactory()->buildScoringInteraction(
                    $this->object->getRefId(),
                    $qid,
                    $this->current_user->getId(),
                    \ilObjTestAccess::_getParticipantId($usr_active_id),
                    TestScoringInteractionTypes::QUESTION_GRADED,
                    [
                        AdditionalInformationGenerator::KEY_REACHED_POINTS => $score,
                        AdditionalInformationGenerator::KEY_FEEDBACK => $feedback,
                        AdditionalInformationGenerator::KEY_EVAL_FINALIZED => $this->logger
                            ->getAdditionalInformationGenerator()->getTrueFalseTagForBool($final)
                    ]
                )
            );
        }
    }

    public function completeScoring(
        int $usr_active_id,
        bool $flag = true
    ) {
        $this->scoring_done_helper->setDone($usr_active_id, $flag);
    }

    public function isScoringComplete(int $usr_active_id): bool
    {
        return $this->scoring_done_helper->isDone($usr_active_id);
    }

}
