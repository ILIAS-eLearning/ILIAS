<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImport.php';
require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImportList.php';

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
     * @var ilTestSkillLevelThresholdImport
     */
    protected $curSkillLevelThreshold = null;
    
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
     * @return ilTestSkillLevelThresholdImportList
     */
    public function getSkillLevelThresholdImportList()
    {
        return $this->skillLevelThresholdImportList;
    }
    
    /**
     */
    public function initSkillLevelThresholdImportList()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $this->skillLevelThresholdImportList = new ilTestSkillLevelThresholdImportList($ilDB);
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
     * @return ilTestSkillLevelThresholdImport
     */
    public function getCurSkillLevelThreshold()
    {
        return $this->curSkillLevelThreshold;
    }
    
    /**
     * @param ilTestSkillLevelThresholdImport $curSkillLevelThreshold
     */
    public function setCurSkillLevelThreshold($curSkillLevelThreshold)
    {
        $this->curSkillLevelThreshold = $curSkillLevelThreshold;
    }
    
    public function setHandlers($xmlParser)
    {
        xml_set_object($xmlParser, $this);
        xml_set_element_handler($xmlParser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($xmlParser, 'handlerCharacterData');
    }
    
    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
    {
        if ($tagName != 'SkillsLevelThresholds' && !$this->isParsingActive()) {
            return;
        }
        
        switch ($tagName) {
            case 'SkillsLevelThresholds':
                $this->setParsingActive(true);
                $this->initSkillLevelThresholdImportList();
                break;
            
            case 'QuestionsAssignedSkill':
                $this->setCurSkillBaseId($tagAttributes['BaseId']);
                $this->setCurSkillTrefId($tagAttributes['TrefId']);
                break;
            
            case 'OriginalSkillTitle':
                $this->resetCharacterDataBuffer();
                break;
            
            case 'OriginalSkillPath':
                $this->resetCharacterDataBuffer();
                break;
            
            case 'SkillLevel':
                global $DIC;
                $ilDB = $DIC['ilDB'];
                $skillLevelThreshold = new ilTestSkillLevelThresholdImport($ilDB);
                $skillLevelThreshold->setImportSkillBaseId($this->getCurSkillBaseId());
                $skillLevelThreshold->setImportSkillTrefId($this->getCurSkillTrefId());
                $skillLevelThreshold->setImportLevelId($tagAttributes['Id']);
                $skillLevelThreshold->setOrderIndex($tagAttributes['Nr']);
                $this->setCurSkillLevelThreshold($skillLevelThreshold);
                break;
            
            case 'ThresholdPercentage':
                $this->resetCharacterDataBuffer();
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
        if (!$this->isParsingActive()) {
            return;
        }
        
        switch ($tagName) {
            case 'SkillsLevelThresholds':
                $this->setParsingActive(false);
                break;
            
            case 'QuestionsAssignedSkill':
                $this->setCurSkillBaseId(null);
                $this->setCurSkillTrefId(null);
                break;
            
            case 'OriginalSkillTitle':
                $this->getSkillLevelThresholdImportList()->addOriginalSkillTitle(
                    $this->getCurSkillBaseId(),
                    $this->getCurSkillTrefId(),
                    $this->getCharacterDataBuffer()
                );
                $this->resetCharacterDataBuffer();
                break;
            
            case 'OriginalSkillPath':
                $this->getSkillLevelThresholdImportList()->addOriginalSkillPath(
                    $this->getCurSkillBaseId(),
                    $this->getCurSkillTrefId(),
                    $this->getCharacterDataBuffer()
                );
                $this->resetCharacterDataBuffer();
                break;
            
            case 'SkillLevel':
                $this->getSkillLevelThresholdImportList()->addSkillLevelThreshold(
                    $this->getCurSkillLevelThreshold()
                );
                $this->setCurSkillLevelThreshold(null);
                break;
            
            case 'ThresholdPercentage':
                $this->getCurSkillLevelThreshold()->setThreshold($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;
            
            case 'OriginalLevelTitle':
                $this->getCurSkillLevelThreshold()->setOriginalLevelTitle($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;
            
            case 'OriginalLevelDescription':
                $this->getCurSkillLevelThreshold()->setOriginalLevelDescription($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;
        }
    }
    
    public function handlerCharacterData($xmlParser, $charData)
    {
        if (!$this->isParsingActive()) {
            return;
        }
        
        if ($charData != "\n") {
            // Replace multiple tabs with one space
            $charData = preg_replace("/\t+/", " ", $charData);
            
            $this->appendToCharacterDataBuffer($charData);
        }
    }
}
