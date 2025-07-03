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

use ILIAS\Skill\Service\SkillProfileService;
use ILIAS\Skill\Service\SkillPersonalService;
use ILIAS\Test\Logging\TestLogger;

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
    private ilAssQuestionSkillAssignmentList $skill_question_assignment_list;
    private ilTestSkillLevelThresholdList $skill_level_threshold_list;
    private array $questions = [];
    private array $max_points_by_question = [];
    private array $reached_points_by_question;
    private array $skill_point_accounts;
    private array $reached_skill_levels;
    private int $user_id;
    private int $active_id;
    private int $pass;
    private int $num_required_bookings_for_skill_triggering;


    public function __construct(
        private ilDBInterface $db,
        private TestLogger $logger,
        int $test_id,
        private int $refId,
        private SkillProfileService $skill_profile_service,
        private SkillPersonalService $skill_personal_service
    ) {
        $this->skill_question_assignment_list = new ilAssQuestionSkillAssignmentList($this->db);

        $this->skill_level_threshold_list = new ilTestSkillLevelThresholdList($this->db);
        $this->skill_level_threshold_list->setTestId($test_id);
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function setActiveId(int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function setPass($pass): void
    {
        $this->pass = $pass;
    }

    public function getNumRequiredBookingsForSkillTriggering(): int
    {
        return $this->num_required_bookings_for_skill_triggering;
    }

    public function setNumRequiredBookingsForSkillTriggering(int $num_required_bookings_for_skill_triggering): void
    {
        $this->num_required_bookings_for_skill_triggering = $num_required_bookings_for_skill_triggering;
    }

    public function init(ilAssQuestionList $question_list): void
    {
        $this->skill_question_assignment_list->setParentObjId($question_list->getParentObjId());
        $this->skill_question_assignment_list->loadFromDb();

        $this->skill_level_threshold_list->loadFromDb();

        $this->initTestQuestionData($question_list);
    }

    /**
     * @param array $test_results An array containing the test results for a given user
     */
    public function evaluate(array $test_results): void
    {
        $this->reset();

        $this->initTestResultData($test_results);

        $this->drawUpSkillPointAccounts();
        $this->evaluateSkillPointAccounts();
    }

    public function getReachedSkillLevels(): array
    {
        return $this->reached_skill_levels;
    }

    private function reset(): void
    {
        $this->reached_points_by_question = [];
        $this->skill_point_accounts = [];
        $this->reached_skill_levels = [];
    }

    private function initTestQuestionData(ilAssQuestionList $question_list): void
    {
        foreach ($question_list->getQuestionDataArray() as $question_data) {
            $this->questions[] = $question_data['question_id'];
            $this->max_points_by_question[ $question_data['question_id'] ] = $question_data['points'];
        }
    }

    private function initTestResultData(array $test_results): void
    {
        foreach ($test_results as $key => $result) {
            if ($key === 'pass' || $key === 'test') { // note: key int 0 IS == 'pass' or 'buxtehude'
                continue;
            }

            $this->reached_points_by_question[ $result['qid'] ] = $result['reached'];
        }
    }

    private function drawUpSkillPointAccounts(): void
    {
        foreach ($this->questions as $question_id) {
            if (!$this->isAnsweredQuestion($question_id)) {
                continue;
            }

            $assignments = $this->skill_question_assignment_list->getAssignmentsByQuestionId($question_id);

            foreach ($assignments as $assignment) {
                if ($assignment->hasEvalModeBySolution()) {
                    $reached_skill_points = $this->determineReachedSkillPointsWithSolutionCompare(
                        $assignment->getSolutionComparisonExpressionList()
                    );
                } else {
                    $max_test_points = $this->max_points_by_question[$question_id];
                    $reached_test_points = $this->reached_points_by_question[$question_id];
                    $reached_skill_points = $this->calculateReachedSkillPointsFromTestPoints(
                        $assignment->getSkillPoints(),
                        $max_test_points,
                        $reached_test_points
                    );
                }

                $this->bookToSkillPointAccount(
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId(),
                    $assignment->getMaxSkillPoints(),
                    $reached_skill_points
                );
            }
        }
    }

    private function isAnsweredQuestion(int $question_id): bool
    {
        return isset($this->reached_points_by_question[$question_id]);
    }

    private function determineReachedSkillPointsWithSolutionCompare(
        ilAssQuestionSolutionComparisonExpressionList $expression_list
    ): ?int {
        $question_provider = new ilAssLacQuestionProvider();
        $question_provider->setQuestionId($expression_list->getQuestionId());

        foreach ($expression_list->get() as $expression) {
            $condition_composite = (new ilAssLacConditionParser())->parse(
                $expression->getExpression()
            );

            $composite_evaluator = new ilAssLacCompositeEvaluator(
                $question_provider,
                $this->getActiveId(),
                $this->getPass()
            );
            if ($composite_evaluator->evaluate($condition_composite)) {
                return $expression->getPoints();
            }
        }

        return 0;
    }

    private function calculateReachedSkillPointsFromTestPoints(
        int $skill_points,
        float $max_test_points,
        float $reached_test_points
    ): float {
        if ($reached_test_points < 0) {
            $reached_test_points = 0;
        }

        $factor = 0;

        if ($max_test_points > 0) {
            $factor = $reached_test_points / $max_test_points;
        }

        return ($skill_points * $factor);
    }

    private function bookToSkillPointAccount(
        int $skill_base_id,
        int $skill_tref_id,
        int $max_skill_points,
        float $reached_skill_points
    ): void {
        $skill_key = $skill_base_id . ':' . $skill_tref_id;

        if (!isset($this->skill_point_accounts[$skill_key])) {
            $this->skill_point_accounts[$skill_key] = new ilTestSkillPointAccount();
        }

        $this->skill_point_accounts[$skill_key]->addBooking($max_skill_points, $reached_skill_points);
    }

    private function evaluateSkillPointAccounts(): void
    {
        foreach ($this->skill_point_accounts as $skill_key => $skill_point_account) {
            if (!$this->doesNumBookingsExceedRequiredBookingsBarrier($skill_point_account)) {
                continue;
            }

            list($skill_base_id, $skill_tref_id) = explode(':', $skill_key);

            $skill = new ilBasicSkill((int) $skill_base_id);
            $levels = $skill->getLevelData();

            $reached_level_id = null;
            foreach ($levels as $level) {
                $threshold = $this->skill_level_threshold_list->getThreshold($skill_base_id, $skill_tref_id, $level['id']);

                if (!($threshold instanceof ilTestSkillLevelThreshold) || $threshold->getThreshold() === null) {
                    continue;
                }

                if ($skill_point_account->getTotalReachedSkillPercent() < $threshold->getThreshold()) {
                    break;
                }

                $reached_level_id = $level['id'];
            }

            $this->reached_skill_levels[] = [
                'sklBaseId' => $skill_base_id, 'sklTrefId' => $skill_tref_id, 'sklLevelId' => $reached_level_id
            ];
        }
    }

    private function doesNumBookingsExceedRequiredBookingsBarrier(ilTestSkillPointAccount $skillPointAccount): bool
    {
        return $skillPointAccount->getNumBookings() >= $this->getNumRequiredBookingsForSkillTriggering();
    }

    public function handleSkillTriggering(): void
    {
        foreach ($this->getReachedSkillLevels() as $reached_skill_level) {
            $this->invokeSkillLevelTrigger((int) $reached_skill_level['sklLevelId'], (int) $reached_skill_level['sklTrefId']);

            if ($reached_skill_level['sklTrefId'] > 0) {
                $this->skill_personal_service->addPersonalSkill($this->getUserId(), (int) $reached_skill_level['sklTrefId']);
            } else {
                $this->skill_personal_service->addPersonalSkill($this->getUserId(), (int) $reached_skill_level['sklBaseId']);
            }
        }
        //write profile completion entries if fulfilment status has changed
        $this->skill_profile_service->writeCompletionEntryForAllProfiles($this->getUserId());
    }

    private function invokeSkillLevelTrigger(int $skill_level_id, int $skill_tref_id): void
    {
        ilBasicSkill::writeUserSkillLevelStatus(
            $skill_level_id,
            $this->getUserId(),
            $this->refId,
            $skill_tref_id,
            ilBasicSkill::ACHIEVED,
            true,
            false,
            (string) $this->getPass()
        );

        $this->logger->info(
            "refId={$this->refId} / usrId={$this->getUserId()} / levelId={$skill_level_id} / trefId={$skill_tref_id}"
        );
    }

    public function getSkillsMatchingNumAnswersBarrier(): array
    {
        $skills_matching_num_answers_barrier = [];

        foreach ($this->skill_point_accounts as $skillKey => $skillPointAccount) {
            if ($this->doesNumBookingsExceedRequiredBookingsBarrier($skillPointAccount)) {
                list($skillBaseId, $skillTrefId) = explode(':', $skillKey);

                $skills_matching_num_answers_barrier[$skillKey] = [
                    'base_skill_id' => (int) $skillBaseId,
                    'tref_id' => (int) $skillTrefId
                ];
            }
        }

        return $skills_matching_num_answers_barrier;
    }

    public function getSkillsInvolvedByAssignment(): array
    {
        $unique_skills = [];

        foreach ($this->skill_question_assignment_list->getUniqueAssignedSkills() as $skill) {
            $skillKey = $skill['skill_base_id'] . ':' . $skill['skill_tref_id'];

            $unique_skills[$skillKey] = [
                'base_skill_id' => (int) $skill['skill_base_id'],
                'tref_id' => (int) $skill['skill_tref_id']
            ];
        }

        return $unique_skills;
    }

    public function isAssignedSkill($skill_base_id, $skill_tref_id): void
    {
        $this->skill_question_assignment_list->isAssignedSkill($skill_base_id, $skill_tref_id);
    }

    public function getAssignedSkillMatchingSkillProfiles(): array
    {
        $matching_skill_profiles = [];
        $users_profiles = $this->skill_profile_service->getProfilesOfUser($this->getUserId());
        foreach ($users_profiles as $profile_data) {
            $assigned_skill_levels = $this->skill_profile_service->getSkillLevels($profile_data->getId());

            foreach ($assigned_skill_levels as $assigned_skill_level) {
                $skill_base_id = $assigned_skill_level->getBaseSkillId();
                $skill_tref_id = $assigned_skill_level->getTrefId();

                if ($this->skill_question_assignment_list->isAssignedSkill($skill_base_id, $skill_tref_id)) {
                    $matching_skill_profiles[$profile_data->getId()] = $profile_data->getTitle();
                }
            }
        }

        return $matching_skill_profiles;
    }

    public function noProfileMatchingAssignedSkillExists(array $available_skill_profiles): bool
    {
        $no_profile_matching_skills = $this->skill_question_assignment_list->getUniqueAssignedSkills();

        foreach (array_keys($available_skill_profiles) as $skill_profile_id) {
            $assigned_skill_levels = $this->skill_profile_service->getSkillLevels(
                $this->skill_profile_service->getProfile($skill_profile_id)->getId()
            );

            foreach ($assigned_skill_levels as $assigned_skill_level) {
                $skill_base_id = $assigned_skill_level->getBaseSkillId();
                $skill_tref_id = $assigned_skill_level->getTrefId();

                if ($this->skill_question_assignment_list->isAssignedSkill($skill_base_id, $skill_tref_id)) {
                    unset($no_profile_matching_skills["{$skill_base_id}:{$skill_tref_id}"]);
                }
            }
        }

        return $no_profile_matching_skills !== [];
    }
}
