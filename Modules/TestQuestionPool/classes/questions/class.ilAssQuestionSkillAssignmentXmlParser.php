<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImport.php';
require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionListImport.php';
require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentXmlParser extends ilSaxParser
{
	/**
	 * @var bool
	 */
	protected $parsingActive;
	
	/**
	 * @var ilAssQuestionSkillAssignmentImport
	 */
	protected $curAssignment;
	
	/**
	 * @var ilAssQuestionSkillAssignmentListImport
	 */
	protected $assignmentList;
	
	/**
	 * @param $xmlFile
	 */
	public function __construct($xmlFile)
	{
		$this->parsingActive = false;
		$this->curAssignment = null;
		$this->assignmentList = new ilAssQuestionSkillAssignmentListImport();
		return parent::ilSaxParser($xmlFile);
	}
	
	/**
	 * @return boolean
	 */
	public function isParsingActive()
	{
		return $this->parsingActive;
	}
	
	/**
	 * @param boolean $parsingActive
	 */
	public function setParsingActive($parsingActive)
	{
		$this->parsingActive = $parsingActive;
	}
	
	/**
	 * @return ilAssQuestionSkillAssignmentImport
	 */
	public function getCurAssignment()
	{
		return $this->curAssignment;
	}
	
	/**
	 * @param ilAssQuestionSkillAssignmentImport $curAssignment
	 */
	public function setCurAssignment($curAssignment)
	{
		$this->curAssignment = $curAssignment;
	}
	
	/**
	 * @return ilAssQuestionSkillAssignmentListImport
	 */
	public function getAssignmentList()
	{
		return $this->assignmentList;
	}
	
	public function setHandlers($xmlParser)
	{
		xml_set_object($xmlParser,$this);
		xml_set_element_handler($xmlParser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($xmlParser,'handlerCharacterData');
	}
	
	public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
	{
		if( $tagName != 'QuestionSkillAssignments' && !$this->isParsingActive() )
		{
			return;
		}
		
		switch($tagName)
		{
			case 'QuestionSkillAssignments':
				$this->setParsingActive(true);
				break;
			
			case 'TriggerQuestion':
				$assignment = new ilAssQuestionSkillAssignmentImport();
				$assignment->setImportQuestionId($tagAttributes['Id']);
				$this->setCurAssignment($assignment);
				break;
		}
	}
	
	public function handlerEndTag($xmlParser, $tagName)
	{
		if( !$this->isParsingActive() )
		{
			return;
		}
		
		switch($tagName)
		{
			case 'QuestionSkillAssignments':
				$this->setParsingActive(false);
				break;
			
			case 'TriggerQuestion':
				$this->getAssignmentList()->add($this->getCurAssignment());
				$this->setCurAssignment(null);
				break;
		}
	}
	
	public function handlerCharacterData($xmlParser, $charData)
	{
		if( !$this->isParsingActive() )
		{
			return;
		}
		
		if( $charData != "\n" )
		{
			// Replace multiple tabs with one space
			$charData = preg_replace("/\t+/"," ",$charData);
			
			$this->cdata .= $charData;
		}
	}
}