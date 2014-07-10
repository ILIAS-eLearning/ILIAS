<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestQuestionSetConfig.php';

/**
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetConfig extends ilTestQuestionSetConfig
{
	const QUESTION_AMOUNT_CONFIG_MODE_PER_TEST = 'TEST';
	const QUESTION_AMOUNT_CONFIG_MODE_PER_POOL = 'POOL';
	
	/**
	 * @var boolean
	 */
	private $requirePoolsWithHomogeneousScoredQuestions = null;
	
	/**
	 * @var string
	 */
	private $questionAmountConfigurationMode = null;
	
	/**
	 * @var integer
	 */
	private $questionAmountPerTest = null;
	
	/**
	 * @var integer
	 */
	private $lastQuestionSyncTimestamp = null;

	/**
	 * @param ilTree $tree
	 * @param ilDB $db
	 * @param ilPluginAdmin $pluginAdmin
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilTree $tree, ilDB $db, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
	{
		parent::__construct($tree, $db, $pluginAdmin, $testOBJ);
	}

	/**
	 * @param boolean $requirePoolsWithHomogeneousScoredQuestions
	 */
	public function setPoolsWithHomogeneousScoredQuestionsRequired($requirePoolsWithHomogeneousScoredQuestions)
	{
		$this->requirePoolsWithHomogeneousScoredQuestions = $requirePoolsWithHomogeneousScoredQuestions;
	}
	
	/**
	 * @return boolean
	 */
	public function arePoolsWithHomogeneousScoredQuestionsRequired()
	{
		return $this->requirePoolsWithHomogeneousScoredQuestions;
	}
	
	/**
	 * @param string $questionAmountConfigurationMode
	 */
	public function setQuestionAmountConfigurationMode($questionAmountConfigurationMode)
	{
		$this->questionAmountConfigurationMode = $questionAmountConfigurationMode;
	}
	
	/**
	 * @return string
	 */
	public function getQuestionAmountConfigurationMode()
	{
		return $this->questionAmountConfigurationMode;
	}
	
	/**
	 * @return boolean
	 */
	public function isQuestionAmountConfigurationModePerPool()
	{
		return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL;
	}
	
	/**
	 * @return boolean
	 */
	public function isQuestionAmountConfigurationModePerTest()
	{
		return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST;
	}
	
	/**
	 * @param integer $questionAmountPerTest
	 */
	public function setQuestionAmountPerTest($questionAmountPerTest)
	{
		$this->questionAmountPerTest = $questionAmountPerTest;
	}
	
	/**
	 * @return integer
	 */
	public function getQuestionAmountPerTest()
	{
		return $this->questionAmountPerTest;
	}
	
	/**
	 * @param integer $lastQuestionSyncTimestamp
	 */
	public function setLastQuestionSyncTimestamp($lastQuestionSyncTimestamp)
	{
		$this->lastQuestionSyncTimestamp = $lastQuestionSyncTimestamp;
	}
	
	/**
	 * @return integer
	 */
	public function getLastQuestionSyncTimestamp()
	{
		return $this->lastQuestionSyncTimestamp;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	/**
	 * initialises the current object instance with values
	 * from matching properties within the passed array
	 * 
	 * @param array $dataArray
	 */
	public function initFromArray($dataArray)
	{
		foreach($dataArray as $field => $value)
		{
			switch($field)
			{
				case 'req_pools_homo_scored':		$this->setPoolsWithHomogeneousScoredQuestionsRequired($value);	break;
				case 'quest_amount_cfg_mode':		$this->setQuestionAmountConfigurationMode($value);				break;
				case 'quest_amount_per_test':		$this->setQuestionAmountPerTest($value);						break;
				case 'quest_sync_timestamp':		$this->setLastQuestionSyncTimestamp($value);					break;
			}
		}
	}
	
	/**
	 * loads the question set config for current test from the database
	 * 
	 * @return boolean
	 */
	public function loadFromDb()
	{
		$res = $this->db->queryF(
				"SELECT * FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
				array('integer'), array($this->testOBJ->getTestId())
		);
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->initFromArray($row);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * saves the question set config for current test to the database
	 * 
	 * @return boolean
	 */
	public function saveToDb()
	{
		if( $this->dbRecordExists($this->testOBJ->getTestId()) )
		{
			$this->updateDbRecord($this->testOBJ->getTestId());
		}
		else
		{
			$this->insertDbRecord($this->testOBJ->getTestId());
		}
	}

	/**
	 * saves the question set config for test with given id to the database
	 *
	 * @param $testId
	 */
	public function cloneToDbForTestId($testId)
	{
		$this->insertDbRecord($testId);
	}

	/**
	 * deletes the question set config for current test from the database
	 */
	public function deleteFromDb()
	{
		$this->db->manipulateF(
				"DELETE FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
				array('integer'), array($this->testOBJ->getTestId())
		);
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * checks wether a question set config for current test exists in the database
	 *
	 * @param $testId
	 * @return boolean
	 */
	private function dbRecordExists($testId)
	{
		$res = $this->db->queryF(
			"SELECT COUNT(*) cnt FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
			array('integer'), array($testId)
		);
		
		$row = $this->db->fetchAssoc($res);
		
		return (bool)$row['cnt'];
	}

	/**
	 * updates the record in the database that corresponds
	 * to the question set config for the current test
	 *
	 * @param $testId
	 */
	private function updateDbRecord($testId)
	{
		$this->db->update('tst_rnd_quest_set_cfg',
			array(
				'req_pools_homo_scored' => array('integer', (int)$this->arePoolsWithHomogeneousScoredQuestionsRequired()),
				'quest_amount_cfg_mode' => array('text', $this->getQuestionAmountConfigurationMode()),
				'quest_amount_per_test' => array('integer', (int)$this->getQuestionAmountPerTest()),
				'quest_sync_timestamp' => array('integer', (int)$this->getLastQuestionSyncTimestamp())
			),
			array(
				'test_fi' => array('integer', $testId)
			)
		);
	}
	
	/**
	 * inserts a new record for the question set config
	 * for the current test into the database
	 *
	 * @param $testId
	 */
	private function insertDbRecord($testId)
	{
		$this->db->insert('tst_rnd_quest_set_cfg', array(
			'test_fi' => array('integer', $testId),
			'req_pools_homo_scored' => array('integer', (int)$this->arePoolsWithHomogeneousScoredQuestionsRequired()),
			'quest_amount_cfg_mode' => array('text', $this->getQuestionAmountConfigurationMode()),
			'quest_amount_per_test' => array('integer', (int)$this->getQuestionAmountPerTest()),
			'quest_sync_timestamp' => array('integer', (int)$this->getLastQuestionSyncTimestamp())
		));
	}

	// -----------------------------------------------------------------------------------------------------------------

	public function isQuestionSetConfigured()
	{
		if( !$this->isQuestionAmountConfigComplete() )
		{
			return false;
		}

		if( !$this->hasSourcePoolDefinitions() )
		{
			return false;
		}

		if( !$this->isQuestionSetBuildable() )
		{
			return false;
		}

		return true;
	}

	public function isQuestionAmountConfigComplete()
	{
		if( $this->isQuestionAmountConfigurationModePerPool() )
		{
			$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

			$sourcePoolDefinitionList->loadDefinitions();

			foreach($sourcePoolDefinitionList as $definition)
			{
				/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

				if( $definition->getQuestionAmount() < 1 )
				{
					return false;
				}
			}
		}
		elseif( $this->getQuestionAmountPerTest() < 1 )
		{
			return false;
		}

		return true;
	}

	public function hasSourcePoolDefinitions()
	{
		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

		return $sourcePoolDefinitionList->savedDefinitionsExist();
	}

	public function isQuestionSetBuildable()
	{
		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
		$sourcePoolDefinitionList->loadDefinitions();

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
		$stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->pluginAdmin);

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilder.php';
		$questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance($this->db, $this->testOBJ, $this, $sourcePoolDefinitionList, $stagingPoolQuestionList);

		return $questionSetBuilder->checkBuildable();
	}
	
	public function doesQuestionSetRelatedDataExist()
	{
		if( $this->dbRecordExists($this->testOBJ->getTestId()) )
		{
			return true;
		}

		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

		if( $sourcePoolDefinitionList->savedDefinitionsExist() )
		{
			return true;
		}

		return false;
	}
	
	public function removeQuestionSetRelatedData()
	{
		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
		$sourcePoolDefinitionList->deleteDefinitions();

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';
		$stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
			$this->db, $this->testOBJ
		);
		$stagingPool->reset();

		$this->resetQuestionSetRelatedTestSettings();

		$this->deleteFromDb();
	}

	public function resetQuestionSetRelatedTestSettings()
	{
		$this->testOBJ->setResultFilterTaxIds(array());
		$this->testOBJ->saveToDb(true);
	}

	/**
	 * removes all question set config related data for cloned/copied test
	 *
	 * @param ilObjTest $cloneTestOBJ
	 */
	public function cloneQuestionSetRelatedData($cloneTestOBJ)
	{
		// clone general config
		
		$this->loadFromDb();
		$this->cloneToDbForTestId($cloneTestOBJ->getTestId());

		// clone source pool definitions (selection rules)

		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
		$sourcePoolDefinitionList->loadDefinitions();
		$sourcePoolDefinitionList->cloneDefinitionsForTestId($cloneTestOBJ->getTestId());

		// build new question stage for cloned test

		$sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($cloneTestOBJ);
		$stagingPool = $this->buildStagingPoolBuilder($cloneTestOBJ);

		$sourcePoolDefinitionList->loadDefinitions();
		$stagingPool->rebuild($sourcePoolDefinitionList);
		$sourcePoolDefinitionList->saveDefinitions();
		
		$this->updateLastQuestionSyncTimestampForTestId($cloneTestOBJ->getTestId(), time());
	}

	private function buildSourcePoolDefinitionList(ilObjTest $testOBJ)
	{
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
		$sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
			$this->db, $testOBJ
		);

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
		$sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
			$this->db, $testOBJ, $sourcePoolDefinitionFactory
		);

		return $sourcePoolDefinitionList;
	}
	
	private function buildStagingPoolBuilder(ilObjTest $testOBJ)
	{
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';
		$stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder($this->db, $testOBJ);
		
		return $stagingPool;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	public function updateLastQuestionSyncTimestampForTestId($testId, $timestamp)
	{
		$this->db->update('tst_rnd_quest_set_cfg',
			array(
				'quest_sync_timestamp' => array('integer', (int)$timestamp)
			),
			array(
				'test_fi' => array('integer', $testId)
			)
		);
	}

	public function isResultTaxonomyFilterSupported()
	{
		return true;
	}

	// -----------------------------------------------------------------------------------------------------------------
	
	public function getSelectableQuestionPools()
	{
		return $this->testOBJ->getAvailableQuestionpools(
			true, $this->arePoolsWithHomogeneousScoredQuestionsRequired(), false, true, true
		);
	}
	
	public function doesSelectableQuestionPoolsExist()
	{
		return (bool)count($this->getSelectableQuestionPools());
	}
}
