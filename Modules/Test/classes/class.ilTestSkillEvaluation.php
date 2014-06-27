<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSkillQuestionAssignmentList.php';
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
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var ilObjTest
	 */
	private $testOBJ;

	/**
	 * @var ilTestSkillQuestionAssignmentList
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

	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;

		$this->skillQuestionAssignmentList = new ilTestSkillQuestionAssignmentList($this->db);
		$this->skillQuestionAssignmentList->setTestId($this->testOBJ->getTestId());

		$this->skillLevelThresholdList = new ilTestSkillLevelThresholdList($this->db);
		$this->skillLevelThresholdList->setTestId($this->testOBJ->getTestId());

		$this->questions = array();
		$this->maxPointsByQuestion = array();
	}

	public function init()
	{
		$this->skillQuestionAssignmentList->loadFromDb();
		$this->skillLevelThresholdList->loadFromDb();

		$this->initTestQuestionData();

		return $this;
	}

	public function evaluate($activeId, $pass, $userId)
	{
		$this->reset();

		$this->initTestResultData($activeId, $pass);

		$this->drawUpSkillPointAccounts();
		$this->evaluateSkillPointAccounts($userId);
	}

	public function trigger($activeId, $pass, $userId)
	{
		$this->evaluate($activeId, $pass, $userId);

		$this->triggerSkillService();
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

	private function initTestQuestionData()
	{
		foreach($this->testOBJ->getTestQuestions() as $question)
		{
			$this->questions[] = $question['question_id'];

			$this->maxPointsByQuestion[ $question['question_id'] ] = $question['points'];
		}
	}

	private function initTestResultData($activeId, $pass)
	{
		$testResults = $this->testOBJ->getTestResult($activeId, $pass, true);
		foreach($testResults as $key => $result)
		{
			if($key === 'pass' || $key === 'test') // note: key int 0 IS == 'pass' or 'buxtehude'
			{
				continue;
			}

			$this->reachedPointsByQuestion[ $result['qid'] ] = $result['reached'];
		}
	}

	private function drawUpSkillPointAccounts()
	{
		foreach($this->questions as $questionId)
		{
			$maxTestPoints = $this->maxPointsByQuestion[$questionId];
			$reachedTestPoints = $this->reachedPointsByQuestion[$questionId];

			$assignments = $this->skillQuestionAssignmentList->getAssignmentsByQuestionId($questionId);

			foreach($assignments as $assignment)
			{
				$reachedSkillPoints = $this->calculateReachedSkillPoints(
					$assignment->getSkillPoints(), $maxTestPoints, $reachedTestPoints
				);

				$this->bookToSkillPointAccount(
					$assignment->getSkillBaseId(), $assignment->getSkillTrefId(), $reachedSkillPoints
				);
			}
		}
	}

	private function calculateReachedSkillPoints($skillPoints, $maxTestPoints, $reachedTestPoints)
	{
		if( $reachedTestPoints < 0 )
		{
			$reachedTestPoints = 0;
		}

		$factor = 0;

		if( $maxTestPoints > 0 )
		{
			$factor = $reachedTestPoints / $maxTestPoints;
		}

		return ( (2 * $skillPoints * $factor) - $skillPoints );
	}

	private function bookToSkillPointAccount($skillBaseId, $skillTrefId, $reachedSkillPoints)
	{
		$skillKey = $skillBaseId.':'.$skillTrefId;

		if( !isset($this->skillPointAccounts[$skillKey]) )
		{
			$this->skillPointAccounts[$skillKey] = 0;
		}

		$this->skillPointAccounts[$skillKey] += $reachedSkillPoints;
	}

	private function evaluateSkillPointAccounts($userId)
	{
		foreach($this->skillPointAccounts as $skillKey => $skillPoints)
		{
			list($skillBaseId, $skillTrefId) = explode(':', $skillKey);

			$skill = new ilBasicSkill($skillBaseId);
			$levels = $skill->getLevelData();

			$reachedLevelId = null;

			foreach($levels as $level)
			{
				$threshold = $this->skillLevelThresholdList->getThreshold($skillBaseId, $skillTrefId, $level['id']);

				if( !($threshold instanceof ilTestSkillLevelThreshold) )
				{
					continue;
				}

				if( $threshold->getThreshold() && $skillPoints >= $threshold->getThreshold() )
				{
					$reachedLevelId = $level['id'];
				}
			}

			if( $reachedLevelId )
			{
				$this->reachedSkillLevels[] = array(
					'usrId' => $userId, 'sklBaseId' => $skillBaseId,
					'sklTrefId' => $skillTrefId, 'sklLevelId' => $reachedLevelId
				);
			}
		}
	}

	private function triggerSkillService()
	{
		foreach($this->getReachedSkillLevels() as $reachedSkillLevel)
		{
			$this->invokeSkillLevelTrigger(
				$reachedSkillLevel['usrId'], $reachedSkillLevel['sklBaseId'],
				$reachedSkillLevel['sklTrefId'], $reachedSkillLevel['sklLevelId']
			);
		}
	}

	private function invokeSkillLevelTrigger($userId, $skillBaseId, $skillTrefId, $skillLevelId)
	{
		ilBasicSkill::writeUserSkillLevelStatus(
			$skillLevelId, $userId, $this->testOBJ->getRefId(), $skillTrefId, ilBasicSkill::ACHIEVED, true
		);

		//mail('bheyser@databay.de', "trigger skill $skillBaseId:$skillTrefId level $skillLevelId for user $userId", '');
	}

	public function getReachedSkillLevelsForPersonalSkillGUI()
	{
		$reachedLevels = array();

		foreach($this->getReachedSkillLevels() as $reachedLevel)
		{
			$reachedLevels[$reachedLevel['sklBaseId']] = array(
				$reachedLevel['sklTrefId'] => $reachedLevel['sklLevelId']
			);
		}

		return $reachedLevels;
	}

	public function getUniqueAssignedSkillsForPersonalSkillGUI()
	{
		$uniqueSkills = array();

		foreach($this->skillQuestionAssignmentList->getUniqueAssignedSkills() as $skill)
		{
			$uniqueSkills[] = array(
				'base_skill_id' => (int)$skill['skill_base_id'],
				'tref_id' => (int)$skill['skill_tref_id']
			);
		}

		return $uniqueSkills;
	}

	public function isAssignedSkill($skillBaseId, $skillTrefId)
	{
		$this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId);
	}

	public function getAssignedSkillMatchingSkillProfiles($usrId)
	{
		$matchingSkillProfiles = array();

		include_once("./Services/Skill/classes/class.ilSkillProfile.php");
		$usersProfiles = ilSkillProfile::getProfilesOfUser($usrId);

		foreach ($usersProfiles as $profileData)
		{
			$profile = new ilSkillProfile($profileData['id']);
			$assignedSkillLevels = $profile->getSkillLevels();

			foreach($assignedSkillLevels as $assignedSkillLevel)
			{
				$skillBaseId = $assignedSkillLevel['base_skill_id'];
				$skillTrefId = $assignedSkillLevel['tref_id'];

				if( $this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId) )
				{
					$matchingSkillProfiles[$profileData['id']] = $profile->getTitle();
				}
			}
		}

		return $matchingSkillProfiles;
	}

	public function noProfileMatchingAssignedSkillExists($usrId, $availableSkillProfiles)
	{
		$noProfileMatchingSkills = $this->skillQuestionAssignmentList->getUniqueAssignedSkills();

		foreach($availableSkillProfiles as $skillProfileId => $skillProfileTitle)
		{
			$profile = new ilSkillProfile($skillProfileId);
			$assignedSkillLevels = $profile->getSkillLevels();

			foreach($assignedSkillLevels as $assignedSkillLevel)
			{
				$skillBaseId = $assignedSkillLevel['base_skill_id'];
				$skillTrefId = $assignedSkillLevel['tref_id'];

				if( $this->skillQuestionAssignmentList->isAssignedSkill($skillBaseId, $skillTrefId) )
				{
					unset($noProfileMatchingSkills["{$skillBaseId}:{$skillTrefId}"]);
				}
			}
		}

		return count($noProfileMatchingSkills);
	}
}