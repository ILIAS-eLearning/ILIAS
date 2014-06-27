<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSkillQuestionAssignment.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillQuestionAssignmentList
{
	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var integer
	 */
	private $testId;

	/**
	 * @var array
	 */
	private $assignments;

	/**
	 * @var array
	 */
	private $numAssignsBySkill;

	/**
	 * @var array
	 */
	private $maxPointsBySkill;

	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @param int $testId
	 */
	public function setTestId($testId)
	{
		$this->testId = $testId;
	}

	/**
	 * @return int
	 */
	public function getTestId()
	{
		return $this->testId;
	}

	public function reset()
	{
		$this->assignments = array();
		$this->numAssignsBySkill = array();
		$this->maxPointsBySkill = array();
	}

	private function addAssignment(ilTestSkillQuestionAssignment $assignment)
	{
		if( !isset($this->assignments[$assignment->getQuestionId()]) )
		{
			$this->assignments[$assignment->getQuestionId()] = array();
		}

		$this->assignments[$assignment->getQuestionId()][] = $assignment;
	}

	private function incrementNumAssignsBySkill(ilTestSkillQuestionAssignment $assignment)
	{
		$key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

		if( !isset($this->numAssignsBySkill[$key]) )
		{
			$this->numAssignsBySkill[$key] = 0;
		}

		$this->numAssignsBySkill[$key]++;
	}

	private function incrementMaxPointsBySkill(ilTestSkillQuestionAssignment $assignment)
	{
		$key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

		if( !isset($this->maxPointsBySkill[$key]) )
		{
			$this->maxPointsBySkill[$key] = 0;
		}

		$this->maxPointsBySkill[$key] += $assignment->getSkillPoints();
	}

	public function loadFromDb()
	{
		$this->reset();

		$query = "
			SELECT test_fi, question_fi, skill_base_fi, skill_tref_fi, skill_points
			FROM tst_skl_qst_assigns
			WHERE test_fi = %s
		";

		$res = $this->db->queryF( $query, array('integer'), array($this->getTestId()) );

		while( $row = $this->db->fetchAssoc($res) )
		{
			$assignment = $this->buildSkillQuestionAssignmentByArray($row);

			$this->addAssignment($assignment);
			$this->incrementNumAssignsBySkill($assignment);
			$this->incrementMaxPointsBySkill($assignment);
		}
	}

	/**
	 * @param array $data
	 * @return ilTestSkillQuestionAssignment
	 */
	private function buildSkillQuestionAssignmentByArray($data)
	{
		$assignment = new ilTestSkillQuestionAssignment($this->db);

		$assignment->setTestId($data['test_fi']);
		$assignment->setQuestionId($data['question_fi']);
		$assignment->setSkillBaseId($data['skill_base_fi']);
		$assignment->setSkillTrefId($data['skill_tref_fi']);
		$assignment->setSkillPoints($data['skill_points']);

		return $assignment;
	}

	private function buildSkillKey($skillBaseId, $skillTrefId)
	{
		return $skillBaseId.':'.$skillTrefId;
	}

	public function loadAdditionalSkillData()
	{
		foreach($this->assignments as $assignmentsByQuestion)
		{
			foreach($assignmentsByQuestion as $assignment)
			{
				$assignment->loadAdditionalSkillData();
			}
		}
	}

	public function getAssignmentsByQuestionId($questionId)
	{
		if( !isset($this->assignments[$questionId]) )
		{
			return array();
		}

		return $this->assignments[$questionId];
	}

	public function getUniqueAssignedSkills()
	{
		require_once 'Services/Skill/classes/class.ilBasicSkill.php';

		$skills = array();

		foreach($this->assignments as $assignmentsByQuestion)
		{
			foreach($assignmentsByQuestion as $assignment)
			{
				$key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

				if( !isset($skills[$key]) )
				{
					$skills[$key] = array(
						'skill' => new ilBasicSkill($assignment->getSkillBaseId()),
						'skill_base_id' => $assignment->getSkillBaseId(),
						'skill_tref_id' => $assignment->getSkillTrefId(),
						'num_assigns' => $this->getNumAssignsBySkill(
							$assignment->getSkillBaseId(), $assignment->getSkillTrefId()
						),
						'max_points' => $this->getMaxPointsBySkill(
								$assignment->getSkillBaseId(), $assignment->getSkillTrefId()
						)
					);
				}
			}
		}

		return $skills;
	}

	public function isAssignedSkill($skillBaseId, $skillTrefId)
	{
		foreach($this->getUniqueAssignedSkills() as $assignedSkill)
		{
			if( $assignedSkill['skill_base_id'] != $skillBaseId )
			{
				continue;
			}

			if( $assignedSkill['skill_tref_id'] == $skillTrefId )
			{
				return true;
			}
		}

		return false;
	}

	public function getNumAssignsBySkill($skillBaseId, $skillTrefId)
	{
		return $this->numAssignsBySkill[$this->buildSkillKey($skillBaseId, $skillTrefId)];
	}

	public function getMaxPointsBySkill($skillBaseId, $skillTrefId)
	{
		return $this->maxPointsBySkill[$this->buildSkillKey($skillBaseId, $skillTrefId)];
	}
}