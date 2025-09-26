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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\TestManScoringDoneHelper;

class ConsecutiveScoring
{
    public function __construct(
        protected readonly \ilObjTest $object,
        protected readonly GeneralQuestionPropertiesRepository $question_repo,
        protected readonly \ilTestShuffler $shuffler,
        protected readonly TestLogger $logger,
        protected TestScoring $scorer,
        protected TestManScoringDoneHelper $scoring_done_helper,
        protected \ilObjUser $current_user,
        protected readonly \ilTestAccess $test_access,
        protected DataFactory $data_factory,
    ) {
    }

    /**
     * @return ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties[]
     */
    public function getManuallyScorableQuestionsInTest(): array
    {
        $qtypes = $this->object->getGlobalSettings()->getDisabledQuestionTypes();
        $ret = [];
        foreach ($this->object->getQuestions() as $qid) {
            $qprops = $this->question_repo->getForQuestionId($qid);
            if (!in_array($qprops->getTypeId(), $qtypes)) {
                $ret[] = $qprops;
            }
        }
        return $ret;
    }

    public function getTestParticipants(): array
    {
        return $this->object->getTestParticipants();
    }

    protected function getQuestionGUI(int $qid): \assQuestionGUI
    {
        return $this->object->createQuestionGUI("", $qid);
    }

    public function getQuestionObject(int $qid): \assQuestion
    {
        return $this->getQuestionGUI($qid)->getObject();
    }

    public function getPassUsedForEvaluation(int $usr_active_id): int
    {
        return $this->object->_getResultPass($usr_active_id);
    }

    public function getUserFullName(
        int $usr_active_id,
        string $pass
    ): string {
        if ($this->object->getAnonymity()
            || !$this->test_access->checkScoreParticipantsAccess()
        ) {
            return \ilObjTest::buildExamId($usr_active_id, $pass, $this->object->getId());
        }
        $user_id = (string) $this->object->_getUserIdFromActiveId($usr_active_id);
        $user_data = $this->object->getUserData([$user_id]);
        $user = $user_data[$user_id];
        return sprintf(
            "%s %s [%s]",
            $user["firstname"],
            $user["lastname"],
            $user["login"]
        );
        /**
        return $this->object->userLookupFullName(
            $this->object->_getUserIdFromActiveId($usr_active_id),
            false,
            true
        );*/
    }

    public function getUserId(
        int $usr_active_id,
        string $pass,
    ): string {
        if ($this->object->getAnonymity()
            || !$this->test_access->checkScoreParticipantsAccess()
        ) {
            return \ilObjTest::buildExamId($usr_active_id, $pass, $this->object->getId());
        }
        return (string) $this->object->_getUserIdFromActiveId($usr_active_id);
    }

    public function getSingleManualFeedback(int $qid, int $usr_active_id, int $pass_id): array
    {
        $fb = $this->object->getSingleManualFeedback($usr_active_id, $qid, $pass_id);
        if (array_key_exists("finalized_tstamp", $fb)) {
            $fb["finalized_time"] = $this->current_user->getDateTimeFormat()->applyTo(
                \DateTimeImmutable::createFromFormat('U', (string) $fb["finalized_tstamp"])
            );
        }
        return $fb;
    }

    public function getUserQuestionGUI(int $qid, int $usr_active_id, int $pass_id): \assQuestionGUI
    {
        $question_gui = $this->getQuestionGUI($qid);
        $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $usr_active_id, $pass_id);
        $question = $question_gui->getObject();
        $question->setShuffler($shuffle_trafo);
        $question_gui->setObject($question);
        return $question_gui;
    }


    /**
     * @return array<int, int[]>, uid => [qids]
     */
    public function getAnsweredQuestionIds(int ...$usr_active_ids): array
    {
        $answered = [];
        foreach ($usr_active_ids as $usr_active_id) {
            $answered[$usr_active_id] = [];

            $pass_id = $this->getPassUsedForEvaluation($usr_active_id);
            $user_results = $this->object->getTestResult(
                $usr_active_id,
                $pass_id,
                false, //$ordered_sequence
                true,//$settings->getShowHiddenQuestions(),
                true//$settings->getShowOptionalQuestions()
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
    public function getFinalizedFeedbackIds(
        array $usr_active_ids,
        array $question_ids
    ): array {
        $finalized = [];
        foreach ($question_ids as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);
            foreach ($usr_active_ids as $uid) {
                if (! array_key_exists($uid, $finalized)) {
                    $finalized[$uid] = [];
                }
                $pass_id = $this->getPassUsedForEvaluation($uid);
                if ((bool) ($feedback[$uid][$pass_id][$qid]['finalized_evaluation'] ?? false)) {
                    $finalized[$uid][] = $qid;
                }
            }
        }
        return $finalized;
    }
    public function getQidsFinalizedBy(
        array $usr_active_ids,
        array $question_ids,
        array $finalizing_usr_ids,
    ): array {
        $finalized = [];
        foreach ($question_ids as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);
            foreach ($usr_active_ids as $uid) {
                if (! array_key_exists($uid, $finalized)) {
                    $finalized[$uid] = [];
                }
                $pass_id = $this->getPassUsedForEvaluation($uid);
                $scorer = $feedback[$uid][$pass_id][$qid]['finalized_by_usr_id'] ?? 0;
                if (in_array($scorer, $finalizing_usr_ids)) {
                    $finalized[$uid][] = $qid;
                }
            }
        }
        return $finalized;
    }


    /**
     * @return int[]
     */
    public function getAllFinalizingUsrIds(): array
    {
        $ret = [];
        $usr_active_ids = array_keys($this->getTestParticipants());
        $question_ids = array_map(
            static fn($q): int => $q->getQuestionId(),
            $this->getManuallyScorableQuestionsInTest()
        );
        foreach ($question_ids as $qid) {
            $feedback = $this->object->getCompleteManualFeedback($qid);

            foreach ($usr_active_ids as $uid) {
                $pass_id = $this->getPassUsedForEvaluation($uid);
                if ($feedback[$uid][$pass_id][$qid]['finalized_by_usr_id'] ?? false) {
                    $ret[] = $feedback[$uid][$pass_id][$qid]['finalized_by_usr_id'];
                }
            }
        }
        return array_unique($ret);
    }

    public function store(
        int $qid,
        int $usr_active_id,
        int $pass_id,
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
            ->getReachedPoints($usr_active_id, $pass_id);
        if ($score !== $previously_reached_points) {
            \assQuestion::_setReachedPoints(
                $usr_active_id,
                $qid,
                $score,
                $max_points,
                $pass_id,
                true
            );
        }

        $this->object->saveManualFeedback(
            $usr_active_id,
            $qid,
            $pass_id,
            $feedback,
            $final
        );

        $this->scorer->setPreserveManualScores(true);
        $this->scorer->recalculateSolution($usr_active_id, $pass_id);

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
