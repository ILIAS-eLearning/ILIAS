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

use ILIAS\DI\LoggingServices;
use ILIAS\Skill\Service\SkillProfileService;
use ILIAS\Skill\Service\SkillPersonalService;

/**
 * Logic for determining a learner’s competences based on the results of a test.
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestSkillEvaluation
{
    private ilAssQuestionSkillAssignmentList $skillQuestionAssignmentList;
    private ilTestSkillLevelThresholdList $skillLevelThresholdList;
    private array $questions = [];
    private array $maxPointsByQuestion = [];
    private array $reachedPointsByQuestion;
    private array $skillPointAccounts;
    private array $reachedSkillLevels;
    private int $userId;
    private int $activeId;
    private int $pass;
    private int $numRequiredBookingsForSkillTriggering;


    public function __construct(
        private ilDBInterface $db,
        private LoggingServices $logging_services,
        int $test_id,
        private int $refId,
        private SkillProfileService $skill_profile_service,
        private SkillPersonalService $skill_personal_service
    ) {
        $this->skillQuestionAssignmentList = new ilAssQuestionSkillAssignmentList($this->db);

        $this->skillLevelThresholdList = new ilTestSkillLevelThresholdList($this->db);
        $this->skillLevelThresholdList->setTestId($test_id);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getActiveId(): int
    {
        return $this->activeId;
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function getNumRequiredBookingsForSkillTriggering(): int
    {
        return $this->numRequiredBookingsForSkillTriggering;
    }

    public function setNumRequiredBookingsForSkillTriggering(int $numRequiredBookingsForSkillTriggering): void
    {
        $this->numRequiredBookingsForSkillTriggering = $numRequiredBookingsForSkillTriggering;
    }

    public function init(ilAssQuestionList $questionList)
    {
        $this->skillQuestionAssignmentList->setParentObjId($questionList->getParentObjId());
        $this->skillQuestionAssignmentList->loadFromDb();

        $this->skillLevelThresholdList->loadFromDb();

        $this->initTestQuestionData($questionList);
    }

    /**
     * @param array $testResults An array containing the test results for a given user
     */
    public function evaluate(array $testResults): void
    {
        $this->reset();

        $this->initTestResultData($testResults);

        $this->drawUpSkillPointAccounts();
        $this->evaluateSkillPointAccounts();
    }

    public function getReachedSkillLevels(): array
    {
        return $this->reachedSkillLevels;
    }

    private function reset()
    {
        $this->reachedPointsByQuestion = array();
        $this->skillPointAccounts = array();
        $this->reachedSkillLevels = array();
    }

    private function initTestQuestionData(ilAssQuestionList $questionList)
    {
        foreach ($questionList->getQuestionDataArray() as $questionData) {
            $this->questions[] = $questionData['question_id'];

            $this->maxPointsByQuestion[ $questionData['question_id'] ] = $questionData['points'];
        }
    }

    /**
     * @param array $testResults
     */
    private function initTestResultData($testResults)
    {
        foreach ($testResults as $key => $result) {
            if ($key === 'pass' || $key === 'test') { // note: key int 0 IS == 'pass' or 'buxtehude'
                continue;
            }

            if (!$result['workedthrough']) {
                continue;
            }

            $this->reachedPointsByQuestion[ $result['qid'] ] = $result['reached'];
        }
    }

    private function drawUpSkillPointAccounts()
    {
        foreach ($this->questions as $questionId) {
            if (!$this->isAnsweredQuestion($questionId)) {
                continue;
            }

            $assignments = $this->skillQuestionAssignmentList->getAssignmentsByQuestionId($questionId);

            foreach ($assignments as $assignment) {
                if ($assignment->hasEvalModeBySolution()) {
                    $reachedSkillPoints = $this->determineReachedSkillPointsWithSolutionCompare(
                        $assignment->getSolutionComparisonExpressionList()
                    );
                } else {
                    $maxTestPoints = $this->maxPointsByQuestion[$questionId];
                    $reachedTestPoints = $this->reachedPointsByQuestion[$questionId];

                    $reachedSkillPoints = $this->calculateReachedSkillPointsFromTestPoints(
                        $assignment->getSkillPoints(),
                        $maxTestPoints,
                        $reachedTestPoints
                    );
                }

                $this->bookToSkillPointAccount(
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId(),
                    $assignment->getMaxSkillPoints(),
                    $reachedSkillPoints
                );
            }
        }
    }

    private function isAnsweredQuestion($questionId): bool
    {
        return isset($this->reachedPointsByQuestion[$questionId]);
    }

    private function determineReachedSkillPointsWithSolutionCompare(ilAssQuestionSolutionComparisonExpressionList $expressionList): ?int
    {
        $questionProvider = new ilAssLacQuestionProvider();
        $questionProvider->setQuestionId($expressionList->getQuestionId());

        foreach ($expressionList->get() as $expression) {
            /* @var ilAssQuestionSolutionComparisonExpression $expression */

            $conditionParser = new ilAssLacConditionParser();
            $conditionComposite = $conditionParser->parse($expression->getExpression());

            $compositeEvaluator = new ilAssLacCompositeEvaluator(
                $questionProvider,
                $this->getActiveId(),
                $this->getPass()
            );
            if ($compositeEvaluator->evaluate($conditionComposite)) {
                return $expression->getPoints();
            }
        }

        return 0;
    }

    private function calculateReachedSkillPointsFromTestPoints($skillPoints, $maxTestPoints, $reachedTestPoints)
    {
        if ($reachedTestPoints < 0) {
            $reachedTestPoints = 0;
        }

        $factor = 0;

        if ($maxTestPoints > 0) {
            $factor = $reachedTestPoints / $maxTestPoints;
        }

        return ($skillPoints * $factor);
    }

    private function bookToSkillPointAccount($skillBaseId, $skillTrefId, $maxSkillPoints, $reachedSkillPoints)
    {
        $skillKey = $skillBaseId . ':' . $skillTrefId;

        if (!isset($this->skillPointAccounts[$skillKey])) {
            $this->skillPointAccounts[$skillKey] = new ilTestSkillPointAccount();
        }

        $this->skillPointAccounts[$skillKey]->addBooking($maxSkillPoints, $reachedSkillPoints);
    }

    private function evaluateSkillPointAccounts()
    {
        foreach ($this->skillPointAccounts as $skillKey => $skillPointAccount) {
            /* @var ilTestSkillPointAccount $skillPointAccount */

            if (!$this->doesNumBookingsExceedRequiredBookingsBarrier($skillPointAccount)) {
                continue;
            }

            list($skillBaseId, $skillTrefId) = explode(':', $skillKey);

            $skill = new ilBasicSkill($skillBaseId);
            $levels = $skill->getLevelData();

            $reachedLevelId = null;

            foreach ($levels as $level) {
                $threshold = $this->skillLevelThresholdList->getThreshold($skillBaseId, $skillTrefId, $level['id']);

                if (!($threshold instanceof ilTestSkillLevelThreshold) || !$threshold->getThreshold()) {
                    continue;
                }

                $reachedLevelId = $level['id'];

                if ($skillPointAccount->getTotalReachedSkillPercent() <= $threshold->getThreshold()) {
                    break;
                }
            }

            if ($reachedLevelId) {
                $this->reachedSkillLevels[] = array(
                    'sklBaseId' => $skillBaseId, 'sklTrefId' => $skillTrefId, 'sklLevelId' => $reachedLevelId
                );
            }
        }
    }

    private function doesNumBookingsExceedRequiredBookingsBarrier(ilTestSkillPointAccount $skillPointAccount): bool
    {
        return $skillPointAccount->getNumBookings() >= $this->getNumRequiredBookingsForSkillTriggering();
    }

    public function handleSkillTriggering()
    {
        foreach ($this->getReachedSkillLevels() as $reachedSkillLevel) {
            $this->invokeSkillLevelTrigger($reachedSkillLevel['sklLevelId'], $reachedSkillLevel['sklTrefId']);

            if ($reachedSkillLevel['sklTrefId'] > 0) {
                $this->skill_personal_service->addPersonalSkill($this->getUserId(), $reachedSkillLevel['sklTrefId']);
            } else {
                $this->skill_personal_service->addPersonalSkill($this->getUserId(), $reachedSkillLevel['sklBaseId']);
            }
        }
        //write profile completion entries if fulfilment status has changed
        $this->skill_profile_service->writeCompletionEntryForAllProfiles($this->getUserId());
    }

    private function invokeSkillLevelTrigger($skillLevelId, $skillTrefId)
    {
        ilBasicSkill::writeUserSkillLevelStatus(
            $skillLevelId,
            $this->getUserId(),
            $this->refId,
            $skillTrefId,
            ilBasicSkill::ACHIEVED,
            true,
            0,
            $this->getPass()
        );

        $this->logging_services->root()->info(
            "refId={$this->refId} / usrId={$this->getUserId()} / levelId={$skillLevelId} / trefId={$skillTrefId}"
        );

        //mail('bheyser@databay.de', "trigger skill level $skillLevelId for user {$this->getUserId()}", '');
    }

    public function getSkillsMatchingNumAnswersBarrier(): array
    {
        $skillsMatchingNumAnswersBarrier = array();

        foreach ($this->skillPointAccounts as $skillKey => $skillPointAccount) {
            if ($this->doesNumBookingsExceedRequiredBookingsBarrier($skillPointAccount)) {
                list($skillBaseId, $skillTrefId) = explode(':', $skillKey);

                $skillsMatchingNumAnswersBarrier[$skillKey] = array(
                    'base_skill_id' => (int) $skillBaseId,
                    'tref_id' => (int) $skillTrefId
                );
            }
        }

        return $skillsMatchingNumAnswersBarrier;
    }

    public function getSkillsInvolvedByAssignment(): array
    {
        $uniqueSkills = array();

        foreach ($this->skillQuestionAssignmentList->getUniqueAssignedSkills() as $skill) {
            $skillKey = $skill['skill_base_id'] . ':' . $skill['skill_tref_id'];

            $uniqueSkills[$skillKey] = array(
                'base_skill_id' => (int) $skill['skill_base_id'],
                'tref_id' => (int) $skill['skill_tref_id']
            );
        }

        return $uniqueSkills;
    }

    public function isAssignedSkill($skillBaseId, $skillTrefId)
    {
        $this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId);
    }

    public function getAssignedSkillMatchingSkillProfiles(): array
    {
        $matchingSkillProfiles = array();

        $usersProfiles = $this->skill_profile_service->getProfilesOfUser($this->getUserId());

        foreach ($usersProfiles as $profileData) {
            $assignedSkillLevels = $this->skill_profile_service->getSkillLevels($profileData->getId());

            foreach ($assignedSkillLevels as $assignedSkillLevel) {
                $skillBaseId = $assignedSkillLevel->getBaseSkillId();
                $skillTrefId = $assignedSkillLevel->getTrefId();

                if ($this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId)) {
                    $matchingSkillProfiles[$profileData->getId()] = $profileData->getTitle();
                }
            }
        }

        return $matchingSkillProfiles;
    }

    public function noProfileMatchingAssignedSkillExists(array $availableSkillProfiles): bool
    {
        $noProfileMatchingSkills = $this->skillQuestionAssignmentList->getUniqueAssignedSkills();

        foreach ($availableSkillProfiles as $skillProfileId => $skillProfileTitle) {
            $profile = $this->skill_profile_service->getProfile($skillProfileId);
            $assignedSkillLevels = $this->skill_profile_service->getSkillLevels($profile->getId());

            foreach ($assignedSkillLevels as $assignedSkillLevel) {
                $skillBaseId = $assignedSkillLevel->getBaseSkillId();
                $skillTrefId = $assignedSkillLevel->getTrefId();

                if ($this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId)) {
                    unset($noProfileMatchingSkills["{$skillBaseId}:{$skillTrefId}"]);
                }
            }
        }

        return $noProfileMatchingSkills !== [];
    }
}
