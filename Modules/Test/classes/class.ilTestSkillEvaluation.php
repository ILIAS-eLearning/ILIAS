<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacQuestionProvider.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacConditionParser.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacCompositeEvaluator.php';
require_once 'Modules/Test/classes/class.ilTestSkillPointAccount.php';
require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
require_once 'Services/Skill/classes/class.ilBasicSkill.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillEvaluation
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var int
     */
    private $refId;

    /**
     * @var ilAssQuestionSkillAssignmentList
     */
    private $skillQuestionAssignmentList;

    /**
     * @var ilTestSkillLevelThresholdList
     */
    private $skillLevelThresholdList;

    /**
     * @var array
     */
    private $questions;

    /**
     * @var array
     */
    private $maxPointsByQuestion;

    /**
     * @var array
     */
    private $reachedPointsByQuestion;

    /**
     * @var array
     */
    private $skillPointAccounts;

    /**
     * @var array
     */
    private $reachedSkillLevels;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $activeId;
    
    /**
     * @var integer
     */
    private $pass;

    /**
     * @var integer
     */
    private $numRequiredBookingsForSkillTriggering;

    public function __construct(ilDBInterface $db, $testId, $refId)
    {
        $this->db = $db;
        $this->refId = $refId;

        $this->skillQuestionAssignmentList = new ilAssQuestionSkillAssignmentList($this->db);

        $this->skillLevelThresholdList = new ilTestSkillLevelThresholdList($this->db);
        $this->skillLevelThresholdList->setTestId($testId);

        $this->questions = array();
        $this->maxPointsByQuestion = array();
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getActiveId()
    {
        return $this->activeId;
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function getNumRequiredBookingsForSkillTriggering()
    {
        return $this->numRequiredBookingsForSkillTriggering;
    }

    public function setNumRequiredBookingsForSkillTriggering($numRequiredBookingsForSkillTriggering)
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
    public function evaluate($testResults)
    {
        $this->reset();

        $this->initTestResultData($testResults);

        $this->drawUpSkillPointAccounts();
        $this->evaluateSkillPointAccounts();
    }

    public function getReachedSkillLevels()
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
    
    private function isAnsweredQuestion($questionId)
    {
        return isset($this->reachedPointsByQuestion[$questionId]);
    }
    
    private function determineReachedSkillPointsWithSolutionCompare(ilAssQuestionSolutionComparisonExpressionList $expressionList)
    {
        $questionProvider  = new ilAssLacQuestionProvider();
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
    
    private function doesNumBookingsExceedRequiredBookingsBarrier(ilTestSkillPointAccount $skillPointAccount)
    {
        return $skillPointAccount->getNumBookings() >= $this->getNumRequiredBookingsForSkillTriggering();
    }

    public function handleSkillTriggering()
    {
        foreach ($this->getReachedSkillLevels() as $reachedSkillLevel) {
            $this->invokeSkillLevelTrigger($reachedSkillLevel['sklLevelId'], $reachedSkillLevel['sklTrefId']);
        }
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
        
        /* @var ILIAS\DI\Container $DIC */ global $DIC;
        
        $DIC->logger()->root()->info(
            "refId={$this->refId} / usrId={$this->getUserId()} / levelId={$skillLevelId} / trefId={$skillTrefId}"
        );

        //mail('bheyser@databay.de', "trigger skill level $skillLevelId for user {$this->getUserId()}", '');
    }
    
    public function getSkillsMatchingNumAnswersBarrier()
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

    public function getSkillsInvolvedByAssignment()
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

    public function getAssignedSkillMatchingSkillProfiles()
    {
        $matchingSkillProfiles = array();

        include_once("./Services/Skill/classes/class.ilSkillProfile.php");
        $usersProfiles = ilSkillProfile::getProfilesOfUser($this->getUserId());

        foreach ($usersProfiles as $profileData) {
            $profile = new ilSkillProfile($profileData['id']);
            $assignedSkillLevels = $profile->getSkillLevels();

            foreach ($assignedSkillLevels as $assignedSkillLevel) {
                $skillBaseId = $assignedSkillLevel['base_skill_id'];
                $skillTrefId = $assignedSkillLevel['tref_id'];

                if ($this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId)) {
                    $matchingSkillProfiles[$profileData['id']] = $profile->getTitle();
                }
            }
        }

        return $matchingSkillProfiles;
    }

    public function noProfileMatchingAssignedSkillExists($availableSkillProfiles)
    {
        $noProfileMatchingSkills = $this->skillQuestionAssignmentList->getUniqueAssignedSkills();

        foreach ($availableSkillProfiles as $skillProfileId => $skillProfileTitle) {
            $profile = new ilSkillProfile($skillProfileId);
            $assignedSkillLevels = $profile->getSkillLevels();

            foreach ($assignedSkillLevels as $assignedSkillLevel) {
                $skillBaseId = $assignedSkillLevel['base_skill_id'];
                $skillTrefId = $assignedSkillLevel['tref_id'];

                if ($this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId)) {
                    unset($noProfileMatchingSkills["{$skillBaseId}:{$skillTrefId}"]);
                }
            }
        }

        return count($noProfileMatchingSkills);
    }
}
