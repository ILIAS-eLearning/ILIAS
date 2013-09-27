<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestRandomQuestionSetBuilder
{
	/**
	 * @var ilDB
	 */
	protected $db = null;

	/**
	 * @var ilObjTest
	 */
	protected $testOBJ = null;

	/**
	 * @var ilTestRandomQuestionSetConfig
	 */
	protected $questionSetConfig = null;

	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionList
	 */
	protected $sourcePoolDefinitionList = null;

	/**
	 * @var ilTestRandomQuestionSetStagingPoolQuestionList
	 */
	protected $stagingPoolQuestionList = null;

	/**
	 * @param ilDB $db
	 * @param ilObjTest $testOBJ
	 * @param ilTestRandomQuestionSetConfig $questionSetConfig
	 * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
	 * @param ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	 */
	protected function __construct(
		ilDB $db, ilObjTest $testOBJ, ilTestRandomQuestionSetConfig $questionSetConfig,
		ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
		ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
		$this->questionSetConfig = $questionSetConfig;
		$this->sourcePoolDefinitionList = $sourcePoolDefinitionList;
		$this->stagingPoolQuestionList = $stagingPoolQuestionList;
	}

	abstract public function checkBuildable();

	abstract public function performBuild(ilTestSession $testSession);

	// =================================================================================================================

	final static public function getInstance(
		ilDB $db, ilObjTest $testOBJ, ilTestRandomQuestionSetConfig $questionSetConfig,
		ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
		ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	)
	{
		if( $questionSetConfig->isQuestionAmountConfigurationModePerPool() )
		{
			require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerPool.php';

			return new ilTestRandomQuestionSetBuilderWithAmountPerPool(
				$db, $testOBJ, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList
			);
		}

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerTest.php';

		return new ilTestRandomQuestionSetBuilderWithAmountPerTest(
			$db, $testOBJ, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList
		);
	}

	// =================================================================================================================



	// =================================================================================================================

	/**
	 * Saves a random question to the database
	 *
	 * @access public
	 * @see $questions
	 */
	function saveRandomQuestion($active_id, $question_id, $pass = NULL, $maxcount)
	{
		global $ilDB;

		if( $pass === null)
		{
			$pass = 0;
		}

		$result = $ilDB->queryF(
			"SELECT test_random_question_id FROM tst_test_rnd_qst WHERE active_fi = %s AND pass = %s",
			array('integer','integer'), array($active_id, $pass)
		);

		if ($result->numRows() < $maxcount)
		{
			$duplicate_id = $question_id;

			if (!$this->isNewRandomTest())
			{
				$duplicate_id = $this->getRandomQuestionDuplicate($question_id, $active_id);
				if ($duplicate_id === FALSE)
				{
					$duplicate_id = $this->duplicateQuestionForTest($question_id);
				}
			}

			$next_id = $ilDB->nextId('tst_test_rnd_qst');

			$ilDB->manipulateF("INSERT INTO tst_test_rnd_qst (test_random_question_id, active_fi, question_fi, sequence, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','integer','integer','integer'),
				array($next_id,$active_id, $duplicate_id, $result->numRows()+1, $pass, time())
			);
		}
	}

	/**
	 * Generates new random questions for the active user
	 *
	 * @access private
	 * @see $questions
	 */
	function generateRandomQuestions($active_id, $pass = NULL)
	{
		$num = $this->getRandomQuestionCount();

		if ($num > 0)
		{
			$qpls =& $this->getRandomQuestionpools();

			$rndquestions = $this->generateRandomPass($num, $qpls, $pass);

			$allquestions = $rndquestions;

			if ($this->getShuffleQuestions())
			{
				shuffle($allquestions);
			}

			$maxcount = 0;
			foreach ($qpls as $data)
			{
				$maxcount += $data["contains"];
			}
			if ($num > $maxcount) $num = $maxcount;
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($active_id, $question_id, $pass, $num);
			}
		}
		else
		{
			$qpls =& $this->getRandomQuestionpools();
			$allquestions = array();
			$maxcount = 0;
			foreach ($qpls as $key => $value)
			{
				if ($value["count"] > 0)
				{
					$rndquestions = $this->generateRandomPass($value["count"], array($value), $pass);
					foreach ($rndquestions as $question_id)
					{
						array_push($allquestions, $question_id);
					}
				}
				$add = ($value["count"] <= $value["contains"]) ? $value["count"] : $value["contains"];
				$maxcount += $add;
			}
			if ($this->getShuffleQuestions())
			{
				shuffle($allquestions);
			}
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($active_id, $question_id, $pass, $maxcount);
			}
		}
	}

	/**
	 * Generates a random test pass for a random test
	 *
	 * @param integer $nr Number of questions to return
	 * @param array $qpls Array of questionpools
	 * @param integer $pass Test pass
	 * @return array A random selection of questions
	 */
	public function generateRandomPass($nr, $qpls, $pass = NULL)
	{
		global $ilDB;

		$qplids = array();

		foreach ($qpls as $arr)
		{
			$qplids[] = $arr['qpl'];
		}

		$result = $ilDB->queryF(
			'SELECT * FROM tst_rnd_cpy WHERE tst_fi = %s AND ' . $ilDB->in('qpl_fi', $qplids, false, 'integer'),
			array('integer'), array($this->getTestId())
		);

		if ($result->numRows())
		{
			$ids = array();

			while ($row = $ilDB->fetchAssoc($result))
			{
				$ids[] = $row['qst_fi'];
			}

			$nr = ($nr > count($ids)) ? count($ids) : $nr;

			$rand_keys = array_rand($ids, $nr);

			$selection = array();

			if (is_array($rand_keys))
			{
				foreach ($rand_keys as $key)
				{
					$selection[$ids[$key]] = $ids[$key];
				}
			}
			else
			{
				$selection[$ids[$rand_keys]] = $ids[$rand_keys];
			}

			return $selection;
		}
		else
		{
			// old style random questions
			return $this->randomSelectQuestions($nr, 0, 1, $qplids, $pass);
		}
	}

	/**
	 * Returns a random selection of questions
	 *
	 * @param integer $nr_of_questions Number of questions to return
	 * @param integer $questionpool ID of questionpool to choose the questions from (0 = all available questionpools)
	 * @param boolean $user_obj_id Use the object id instead of the reference id when set to true
	 * @param array $qpls An array of questionpool id's if the random questions should only be chose from the contained questionpools
	 * @return array A random selection of questions
	 *
	 * @deprecated !! this is required for oldOldSchool Tests ONLY (we want to abandon the backwards compatibility)
	 */
	function randomSelectQuestions($nr_of_questions, $questionpool, $use_obj_id = 0, $qpls = "", $pass = NULL)
	{
		global $ilDB;

		// retrieve object id instead of ref id if necessary
		if (($questionpool != 0) && (!$use_obj_id)) $questionpool = ilObject::_lookupObjId($questionpool);

		// get original ids of all existing questions in the test
		$result = $ilDB->queryF("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND qpl_questions.tstamp > 0 AND tst_test_question.test_fi = %s",
			array("integer"),
			array($this->getTestId())
		);
		$original_ids = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($original_ids, $row['original_id']);
		}

		$available = "";
		// get a list of all available questionpools
		if (($questionpool == 0) && (!is_array($qpls)))
		{
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			$available_pools = array_keys(ilObjQuestionPool::_getAvailableQuestionpools($use_object_id = TRUE, $equal_points = FALSE, $could_be_offline = FALSE, $showPath = FALSE, $with_questioncount = FALSE, "read", ilObject::_lookupOwner($this->getId())));
			if (count($available_pools))
			{
				$available = " AND " . $ilDB->in('obj_fi', $available_pools, false, 'integer');
			}
			else
			{
				return array();
			}
		}

		$constraint_qpls = "";
		if ($questionpool == 0)
		{
			if (is_array($qpls))
			{
				if (count($qpls) > 0)
				{
					$constraint_qpls = " AND " . $ilDB->in('obj_fi', $qpls, false, 'integer');
				}
			}
		}

		$original_clause = "";
		if (count($original_ids))
		{
			$original_clause = " AND " . $ilDB->in('question_id', $original_ids, true, 'integer');
		}

		if ($questionpool == 0)
		{
			$result = $ilDB->queryF("SELECT question_id FROM qpl_questions WHERE original_id IS NULL $available $constraint_qpls AND owner > %s AND complete = %s $original_clause",
				array('integer', 'text'),
				array(0, "1")
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT question_id FROM qpl_questions WHERE original_id IS NULL AND obj_fi = %s AND owner > %s AND complete = %s $original_clause",
				array('integer','integer', 'text'),
				array($questionpool, 0, "1")
			);
		}
		$found_ids = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$found_ids[] = $row['question_id'];
		}
		$nr_of_questions = ($nr_of_questions > count($found_ids)) ? count($found_ids) : $nr_of_questions;
		if ($nr_of_questions == 0) return array();
		$rand_keys = array_rand($found_ids, $nr_of_questions);
		$result = array();
		if (is_array($rand_keys))
		{
			foreach ($rand_keys as $key)
			{
				$result[$found_ids[$key]] = $found_ids[$key];
			}
		}
		else
		{
			$result[$found_ids[$rand_keys]] = $found_ids[$rand_keys];
		}
		return $result;
	}

	/**
	 * Checks wheather the test is a new random test (using tst_rnd_cpy) or an old one
	 */
	protected function isNewRandomTest()
	{
		global $ilDB;
		$result = $ilDB->queryF('SELECT copy_id FROM tst_rnd_cpy WHERE tst_fi = %s',
			array('integer'),
			array($this->getTestId())
		);
		return $result->numRows() > 0;
	}
	/**
	 * Returns the question id of the duplicate of a question which is already in use in a random test
	 *
	 * @param integer $question_id Question ID of the original question
	 * @param integer $active_id Active ID of the user
	 * @return mixed The question ID of the duplicate or FALSE if no duplicate was found
	 * @access public
	 * @see $questions
	 */
	function getRandomQuestionDuplicate($question_id, $active_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.question_id FROM qpl_questions, tst_test_rnd_qst WHERE qpl_questions.original_id = %s AND tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.active_fi = %s",
			array('integer', 'integer'),
			array($question_id, $active_id)
		);
		$num = $result->numRows();
		if ($num > 0)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["question_id"];
		}
		else
		{
			return FALSE;
		}
	}
}