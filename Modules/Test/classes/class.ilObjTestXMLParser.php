<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilObjTestXMLParser extends ilSaxParser
{
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;

	/**
	 * @var ilImportMapping
	 */
	protected $importMapping;

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

	/**
	 * @return ilImportMapping
	 */
	public function getImportMapping()
	{
		return $this->importMapping;
	}

	/**
	 * @param ilImportMapping $importMapping
	 */
	public function setImportMapping($importMapping)
	{
		$this->importMapping = $importMapping;
	}
	
	public function setHandlers($xmlParser)
	{
		xml_set_object($xmlParser,$this);
		xml_set_element_handler($xmlParser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($xmlParser,'handlerCharacterData');
	}

	public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
	{
		switch($tagName)
		{
			case 'RandomQuestionSetConfig':
				$this->inRandomQuestionSetConfig = true;
				break;
			
			case 'RandomQuestionSetSettings':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionSetSettings = true;
					$this->cdata = '';
					$this->attr = $tagAttributes;
				}
				break;

			case 'RandomQuestionStage':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionStage = true;
				}
				break;

			case 'RandomQuestionStagingPool':
				if($this->inRandomQuestionStage)
				{
					$this->cdata = '';
					$this->attr = $tagAttributes;
				}
				break;
			
			case 'RandomQuestionSelectionDefinitions':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionSelectionDefinitions = true;
				}
				break;

			case 'RandomQuestionSelectionDefinition':
				if($this->inRandomQuestionSelectionDefinitions)
				{
					$this->sourcePoolDefinition = $this->getRandomQuestionSourcePoolDefinitionInstance();
					$this->attr = $tagAttributes;
				}
				break;
			
			case 'RandomQuestionSourcePoolTitle':
			case 'RandomQuestionSourcePoolPath':
				if($this->sourcePoolDefinition instanceof ilTestRandomQuestionSetSourcePoolDefinition)
				{
					$this->cdata = '';
				}
				break;
		}
	}

	public function handlerEndTag($xmlParser, $tagName)
	{
		switch($tagName)
		{
			case 'RandomQuestionSetConfig':
				$this->inRandomQuestionSetConfig = false;
				break;

			case 'RandomQuestionSetSettings':
				if($this->inRandomQuestionSetConfig)
				{
					$this->importRandomQuestionSetSettings($this->attr);
					$this->attr = null;
				}
				break;

			case 'RandomQuestionStage':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionStage = false;
				}
				break;

			case 'RandomQuestionStagingPool':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionStage)
				{
					$this->importRandomQuestionStagingPool($this->attr, $this->cdata);
					$this->attr = null;
					$this->cdata = '';
				}
				break;

			case 'RandomQuestionSelectionDefinitions':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionSelectionDefinitions = false;
				}
				break;

			case 'RandomQuestionSelectionDefinition':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionSelectionDefinitions)
				{
					$this->importRandomQuestionSourcePoolDefinition($this->sourcePoolDefinition, $this->attr);
					$this->sourcePoolDefinition->saveToDb();
					
					$this->getImportMapping()->addMapping(
						'Modules/Test', 'rnd_src_pool_def', $this->attr['id'], $this->sourcePoolDefinition->getId()
					);
					
					$this->sourcePoolDefinition = null;
					$this->attr = null;
				}
				break;

			case 'RandomQuestionSourcePoolTitle':
				if($this->sourcePoolDefinition instanceof ilTestRandomQuestionSetSourcePoolDefinition)
				{
					$this->sourcePoolDefinition->setPoolTitle($this->cdata);
					$this->cdata = '';
				}
				break;

			case 'RandomQuestionSourcePoolPath':
				if($this->sourcePoolDefinition instanceof ilTestRandomQuestionSetSourcePoolDefinition)
				{
					$this->sourcePoolDefinition->setPoolPath($this->cdata);
					$this->cdata = '';
				}
				break;
		}
	}

	public function handlerCharacterData($xmlParser, $charData)
	{
		if( $charData != "\n" )
		{
			// Replace multiple tabs with one space
			$charData = preg_replace("/\t+/"," ",$charData);

			$this->cdata .= $charData;
		}
	}
	
	protected function importRandomQuestionSetSettings($attr)
	{
		global $tree, $ilDB, $ilPluginAdmin;

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
		$questionSetConfig = new ilTestRandomQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->testOBJ);

		if( !$questionSetConfig->isValidQuestionAmountConfigurationMode($attr['amountMode']) )
		{
			throw new ilTestException(
				'invalid random test question set config amount mode given: "'.$attr['amountMode'].'"'
			);
		}
		
		$questionSetConfig->setQuestionAmountConfigurationMode($attr['amountMode']);
		$questionSetConfig->setQuestionAmountPerTest((int)$attr['questAmount']);
		$questionSetConfig->setPoolsWithHomogeneousScoredQuestionsRequired((bool)$attr['homogeneous']);

		$questionSetConfig->saveToDb();
	}
	
	protected function importRandomQuestionStagingPool($attr, $cdata)
	{
		global $ilDB;
		
		$oldPoolId = $attr['poolId'];
		$newPoolId = $ilDB->nextId('object_data'); // yes !!
		
		$this->getImportMapping()->addMapping(
			'Modules/Test', 'pool', $oldPoolId, $newPoolId
		);
		
		$oldQuestionIds = explode(',', $cdata);
		
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestion.php';
		
		foreach($oldQuestionIds as $oldQuestionId)
		{
			$newQuestionId = $this->getImportMapping()->getMapping(
				'Modules/Test', 'quest', $oldQuestionId
			);
			
			$stagingQuestion = new ilTestRandomQuestionSetStagingPoolQuestion($ilDB);
			$stagingQuestion->setTestId($this->testOBJ->getTestId());
			$stagingQuestion->setPoolId($newPoolId);
			$stagingQuestion->setQuestionId($newQuestionId);

			$stagingQuestion->saveQuestionStaging();
		}
	}
	
	protected function getRandomQuestionSourcePoolDefinitionInstance()
	{
		global $ilDB;

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinition.php';
		
		return new ilTestRandomQuestionSetSourcePoolDefinition($ilDB, $this->testOBJ);
	}

	protected function importRandomQuestionSourcePoolDefinition(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition, $attr)
	{
		$sourcePoolDefinition->setPoolId($this->getImportMapping()->getMapping(
			'Modules/Test', 'pool', (int)$attr['poolId']
		));

		$sourcePoolDefinition->setPoolQuestionCount((int)$attr['poolQuestCount']);
		$sourcePoolDefinition->setQuestionAmount((int)$attr['questAmount']);
		$sourcePoolDefinition->setSequencePosition((int)$attr['position']);

		if( isset($attr['tax']) && isset($attr['taxNode']) )
		{
			$sourcePoolDefinition->setMappedFilterTaxId((int)$attr['tax']);
			$sourcePoolDefinition->setMappedFilterTaxNodeId((int)$attr['taxNode']);
		}
	}
}
