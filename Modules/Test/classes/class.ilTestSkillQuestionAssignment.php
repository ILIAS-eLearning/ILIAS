<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillQuestionAssignment
{
	const DEFAULT_COMPETENCE_POINTS = 1;

	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var integer
	 */
	private $testId;

	/**
	 * @var integer
	 */
	private $questionId;

	/**
	 * @var integer
	 */
	private $skillBaseId;

	/**
	 * @var integer
	 */
	private $skillTrefId;

	/**
	 * @var integer
	 */
	private $skillPoints;

	/**
	 * @var string
	 */
	private $skillTitle;

	/**
	 * @var string
	 */
	private $skillPath;


	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}

	public function loadFromDb()
	{
		$query = "
			SELECT test_fi, question_fi, skill_base_fi, skill_tref_fi, skill_points
			FROM tst_skl_qst_assigns
			WHERE test_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

		$res = $this->db->queryF(
			$query, array('integer', 'integer', 'integer', 'integer'),
			array($this->getTestId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
		);

		$row = $this->db->fetchAssoc($res);

		if( is_array($row) )
		{
			$this->setSkillPoints($row['skill_points']);
		}
	}

	public function saveToDb()
	{
		if( $this->dbRecordExists() )
		{
			$this->db->update('tst_skl_qst_assigns', array(
					'skill_points' => array('integer', $this->getSkillPoints())
				),
				array(
					'test_fi' => array('integer', $this->getTestId()),
					'question_fi' => array('integer', $this->getQuestionId()),
					'skill_base_fi' => array('integer', $this->getSkillBaseId()),
					'skill_tref_fi' => array('integer', $this->getSkillTrefId())
				)
			);
		}
		else
		{
			$this->db->insert('tst_skl_qst_assigns', array(
				'test_fi' => array('integer', $this->getTestId()),
				'question_fi' => array('integer', $this->getQuestionId()),
				'skill_base_fi' => array('integer', $this->getSkillBaseId()),
				'skill_tref_fi' => array('integer', $this->getSkillTrefId()),
				'skill_points' => array('integer', $this->getSkillPoints())
			));
		}
	}

	public function deleteFromDb()
	{
		$query = "
			DELETE FROM tst_skl_qst_assigns
			WHERE test_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

		$this->db->manipulateF(
			$query, array('integer', 'integer', 'integer', 'integer'),
			array($this->getTestId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
		);
	}

	public function dbRecordExists()
	{
		$query = "
			SELECT COUNT(*) cnt
			FROM tst_skl_qst_assigns
			WHERE test_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

		$res = $this->db->queryF(
			$query, array('integer', 'integer', 'integer', 'integer'),
			array($this->getTestId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
		);

		$row = $this->db->fetchAssoc($res);

		return $row['cnt'] > 0;
	}

	/**
	 * @param int $skillPoints
	 */
	public function setSkillPoints($skillPoints)
	{
		$this->skillPoints = $skillPoints;
	}

	/**
	 * @return int
	 */
	public function getSkillPoints()
	{
		return $this->skillPoints;
	}

	/**
	 * @param int $questionId
	 */
	public function setQuestionId($questionId)
	{
		$this->questionId = $questionId;
	}

	/**
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->questionId;
	}

	/**
	 * @param int $skillBaseId
	 */
	public function setSkillBaseId($skillBaseId)
	{
		$this->skillBaseId = $skillBaseId;
	}

	/**
	 * @return int
	 */
	public function getSkillBaseId()
	{
		return $this->skillBaseId;
	}

	/**
	 * @param int $skillTrefId
	 */
	public function setSkillTrefId($skillTrefId)
	{
		$this->skillTrefId = $skillTrefId;
	}

	/**
	 * @return int
	 */
	public function getSkillTrefId()
	{
		return $this->skillTrefId;
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

	public function loadAdditionalSkillData()
	{
		require_once 'Services/Skill/classes/class.ilBasicSkill.php';
		require_once 'Services/Skill/classes/class.ilSkillTree.php';

		$this->setSkillTitle(
			ilBasicSkill::_lookupTitle($this->getSkillBaseId(), $this->getSkillTrefId())
		);

		$tree = new ilSkillTree();

		$path = $tree->getSkillTreePath(
			$this->getSkillBaseId(), $this->getSkillTrefId()
		);

		$nodes = array();
		foreach ($path as $node)
		{
			if( $node['child'] > 1 && $node['skill_id'] != $this->getSkillBaseId() )
			{
				$nodes[] = $node['title'];
			}
		}

		$this->setSkillPath(implode(' > ', $nodes));
	}

	public function setSkillTitle($skillTitle)
	{
		$this->skillTitle = $skillTitle;
	}

	public function getSkillTitle()
	{
		return $this->skillTitle;
	}

	public function setSkillPath($skillPath)
	{
		$this->skillPath = $skillPath;
	}

	public function getSkillPath()
	{
		return $this->skillPath;
	}
}