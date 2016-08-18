<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestExport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestExportRandomQuestionSet extends ilTestExport
{
	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionList
	 */
	protected $srcPoolDefList;
	
	protected function initXmlExport()
	{
		global $ilDB, $ilPluginAdmin;

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
		$srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
			$ilDB, $this->test_obj
		);
		
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
		$this->srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
			$ilDB, $this->test_obj, $srcPoolDefFactory
		);

		$this->srcPoolDefList->loadDefinitions();
	}

	protected function getQuestionsQtiXml()
	{
		global $ilDB, $ilPluginAdmin;
		
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
		$questionList = new ilTestRandomQuestionSetStagingPoolQuestionList($ilDB, $ilPluginAdmin);
		
		$questionQtiXml = '';

		foreach($this->srcPoolDefList->getInvolvedSourcePoolIds() as $poolId)
		{
			$questionList->resetQuestionList();
			$questionList->setTestId($this->test_obj->getTestId());
			$questionList->setPoolId($poolId);
			$questionList->loadQuestions();
			
			foreach ($questionList as $questionId)
			{
				$questionQtiXml .= $this->getQuestionQtiXml($questionId);
			}
		}

		return $questionQtiXml;
	}
}