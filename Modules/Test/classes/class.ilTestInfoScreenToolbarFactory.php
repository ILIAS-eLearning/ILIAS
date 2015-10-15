<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
require_once 'Modules/Test/classes/class.ilTestPlayerFactory.php';
require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
require_once 'Modules/Test/classes/class.ilTestSequenceFactory.php';
require_once 'Modules/Test/classes/class.ilTestDynamicQuestionSetFilterSelection.php';
require_once 'Modules/Test/classes/toolbars/class.ilTestInfoScreenToolbarGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestInfoScreenToolbarFactory
{
	/**
	 * @var integer
	 */
	private $testRefId;
	
	/**
	 * @var ilObjTest
	 */
	private $testOBJ;

	/**
	 * @var ilTestQuestionSetConfigFactory
	 */
	private $testQuestionSetConfigFactory;

	/**
	 * @var ilTestPlayerFactory
	 */
	private $testPlayerFactory;

	/**
	 * @var ilTestSessionFactory
	 */
	private $testSessionFactory;

	/**
	 * @var ilTestSequenceFactory
	 */
	private $testSequenceFactory;

	/**
	 * @return int
	 */
	public function getTestRefId()
	{
		return $this->testRefId;
	}

	/**
	 * @param int $testRefId
	 */
	public function setTestRefId($testRefId)
	{
		$this->testRefId = $testRefId;
	}

	/**
	 * @return ilObjTest
	 */
	public function getTestOBJ()
	{
		return $this->testOBJ;
	}

	/**
	 * @param ilObjTest $testOBJ
	 */
	public function setTestOBJ($testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}
	
	protected function ensureInitialised()
	{
		$this->ensureTestObjectInitialised();
		
		global $tree, $ilDB, $ilPluginAdmin, $lng;

		$this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $this->getTestOBJ());
		$this->testPlayerFactory = new ilTestPlayerFactory($this->getTestOBJ());
		$this->testSessionFactory = new ilTestSessionFactory($this->getTestOBJ());
		$this->testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->getTestOBJ());
	}
	
	private function ensureTestObjectInitialised()
	{
		if( !($this->testOBJ instanceof ilObjTest) )
		{
			$this->testOBJ = ilObjectFactory::getInstanceByRefId($this->testRefId);
		}
	}
	
	public function getToolbarInstance()
	{
		global $ilDB, $ilAccess, $ilCtrl, $lng;
		
		$this->ensureInitialised();

		$toolbar = new ilTestInfoScreenToolbarGUI($ilDB, $ilAccess, $ilCtrl, $lng);
		
		$toolbar->setTestOBJ($this->getTestOBJ());
		$toolbar->setTestPlayerGUI($this->testPlayerFactory->getPlayerGUI());

		$testQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
		$testSession = $this->testSessionFactory->getSession();
		$testSequence = $this->testSequenceFactory->getSequenceByTestSession($testSession);
		$testSequence->loadFromDb();
		$testSequence->loadQuestions($testQuestionSetConfig, new ilTestDynamicQuestionSetFilterSelection());

		$toolbar->setTestQuestionSetConfig($testQuestionSetConfig);
		$toolbar->setTestSession($testSession);
		$toolbar->setTestSequence($testSequence);
		
		return $toolbar;
	}
}