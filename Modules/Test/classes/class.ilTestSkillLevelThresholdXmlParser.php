<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImport.php';
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdXmlParser extends ilSaxParser
{
	/**
	 * @var bool
	 */
	protected $parsingActive = false;
	
	/**
	 * @var string
	 */
	protected $characterDataBuffer = null;
	
	/**
	 * @var ilTestSkillLevelThresholdList
	 */
	protected $skillLevelThresholdImportList = null;
	
	/**
	 * @var integer
	 */
	protected $curSkillBaseId = null;
	
	/**
	 * @var integer
	 */
	protected $curSkillTrefId = null;
	
	/**
	 * @var string
	 */
	protected $curOriginalSkillTitle = null;
	
	/**
	 * @var string
	 */
	protected $curOriginalSkillPath = null;
	
	/**
	 * @var string
	 */
	protected $curOriginalLevelTitle = null;
	
	/**
	 * @var string
	 */
	protected $curOriginalLevelDescription = null;
	
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
	 * @return string
	 */
	protected function getCharacterDataBuffer()
	{
		return $this->characterDataBuffer;
	}
	
	/**
	 * @param string $characterDataBuffer
	 */
	protected function resetCharacterDataBuffer()
	{
		$this->characterDataBuffer = '';
	}
	
	/**
	 * @param string $characterData
	 */
	protected function appendToCharacterDataBuffer($characterData)
	{
		$this->characterDataBuffer .= $characterData;
	}
	
	/**
	 * @return ilTestSkillLevelThresholdList
	 */
	public function getSkillLevelThresholdImportList()
	{
		return $this->skillLevelThresholdImportList;
	}
	
	/**
	 */
	public function initSkillLevelThresholdImportList()
	{
		global $ilDB;
		$this->skillLevelThresholdImportList = new ilTestSkillLevelThresholdList($ilDB);
	}
	
	/**
	 * @return int
	 */
	public function getCurSkillBaseId()
	{
		return $this->curSkillBaseId;
	}
	
	/**
	 * @param int $curSkillBaseId
	 */
	public function setCurSkillBaseId($curSkillBaseId)
	{
		$this->curSkillBaseId = $curSkillBaseId;
	}
	
	/**
	 * @return int
	 */
	public function getCurSkillTrefId()
	{
		return $this->curSkillTrefId;
	}
	
	/**
	 * @param int $curSkillTrefId
	 */
	public function setCurSkillTrefId($curSkillTrefId)
	{
		$this->curSkillTrefId = $curSkillTrefId;
	}
	
	/**
	 * @return string
	 */
	public function getCurOriginalSkillTitle()
	{
		return $this->curOriginalSkillTitle;
	}
	
	/**
	 * @param string $curOriginalSkillTitle
	 */
	public function setCurOriginalSkillTitle($curOriginalSkillTitle)
	{
		$this->curOriginalSkillTitle = $curOriginalSkillTitle;
	}
	
	/**
	 * @return string
	 */
	public function getCurOriginalSkillPath()
	{
		return $this->curOriginalSkillPath;
	}
	
	/**
	 * @param string $curOriginalSkillPath
	 */
	public function setCurOriginalSkillPath($curOriginalSkillPath)
	{
		$this->curOriginalSkillPath = $curOriginalSkillPath;
	}
	
	/**
	 * @return string
	 */
	public function getCurOriginalLevelTitle()
	{
		return $this->curOriginalLevelTitle;
	}
	
	/**
	 * @param string $curOriginalLevelTitle
	 */
	public function setCurOriginalLevelTitle($curOriginalLevelTitle)
	{
		$this->curOriginalLevelTitle = $curOriginalLevelTitle;
	}
	
	/**
	 * @return string
	 */
	public function getCurOriginalLevelDescription()
	{
		return $this->curOriginalLevelDescription;
	}
	
	/**
	 * @param string $curOriginalLevelDescription
	 */
	public function setCurOriginalLevelDescription($curOriginalLevelDescription)
	{
		$this->curOriginalLevelDescription = $curOriginalLevelDescription;
	}
	
	public function setHandlers($xmlParser)
	{
		xml_set_object($xmlParser, $this);
		xml_set_element_handler($xmlParser, 'handlerBeginTag', 'handlerEndTag');
		xml_set_character_data_handler($xmlParser, 'handlerCharacterData');
	}
	
	public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
	{
		if ($tagName != 'SkillsLevelThresholds' && !$this->isParsingActive())
		{
			return;
		}
		
		switch ($tagName) {
			case 'SkillsLevelThresholds':
				$this->setParsingActive(true);
				$this->initSkillLevelThresholdImportList();
				break;
			
			case 'QuestionsAssignedSkill':
				$this->setCurSkillBaseId($tagAttributes['SkillBaseId']);
				$this->setCurSkillTrefId($tagAttributes['SkillTrefId']);
				break;
			
			case 'OriginalSkillTitle':
				$this->resetCharacterDataBuffer();
				break;
			
			case 'OriginalSkillPath':
				$this->resetCharacterDataBuffer();
				break;
			
			case 'SkillLevel':
				global $ilDB;
				$skillLevelThreshold = new ilTestSkillLevelThresholdImport($ilDB);
				$skillLevelThreshold->setImportSkillBaseId($this->getCurSkillBaseId());
				$skillLevelThreshold->setImportSkillTrefId($this->getCurSkillTrefId());
				// add to cur
				break;
			
			case 'SkillPointsThreshold':
				break;
			
			case 'OriginalLevelTitle':
				$this->resetCharacterDataBuffer();
				break;
			
			case 'OriginalLevelDescription':
				$this->resetCharacterDataBuffer();
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
			case 'SkillsLevelThresholds':
				$this->setParsingActive(false);
				break;
			
			case 'QuestionsAssignedSkill':
				$this->setCurSkillBaseId(null);
				$this->setCurSkillTrefId(null);
				break;
			
			case 'OriginalSkillTitle':
				$this->getSkillLevelThresholdImportList()->addOriginalSkillTitle(
					$this->getCurSkillBaseId(), $this->getCurSkillTrefId(), $this->getCharacterDataBuffer()
				);
				$this->resetCharacterDataBuffer();
				break;
			
			case 'OriginalSkillPath':
				$this->getSkillLevelThresholdImportList()->addOriginalSkillPath(
					$this->getCurSkillBaseId(), $this->getCurSkillTrefId(), $this->getCharacterDataBuffer()
				);
				$this->resetCharacterDataBuffer();
				$this->resetCharacterDataBuffer();
				break;
			
			case 'SkillLevel':
				break;
			
			case 'SkillPointsThreshold':
				break;
			
			case 'OriginalLevelTitle':
				$this->resetCharacterDataBuffer();
				break;
			
			case 'OriginalLevelDescription':
				$this->resetCharacterDataBuffer();
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
			
			$this->appendToCharacterDataBuffer($charData);
		}
	}
}