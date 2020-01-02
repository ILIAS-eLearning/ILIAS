<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportList implements Iterator
{
    protected $originalSkillTitles = array();
    protected $originalSkillPaths = array();
    protected $importedSkillLevelThresholds = array();
    
    public function addOriginalSkillTitle($skillBaseId, $skillTrefId, $originalSkillTitle)
    {
        $this->originalSkillTitles["{$skillBaseId}:{$skillTrefId}"] = $originalSkillTitle;
    }
    
    public function addOriginalSkillPath($skillBaseId, $skillTrefId, $originalSkillPath)
    {
        $this->originalSkillPaths["{$skillBaseId}:{$skillTrefId}"] = $originalSkillPath;
    }
    
    public function addSkillLevelThreshold(ilTestSkillLevelThresholdImport $importedSkillLevelThreshold)
    {
        $this->importedSkillLevelThresholds[] = $importedSkillLevelThreshold;
    }
    
    public function getThresholdsByImportSkill($importSkillBaseId, $importSkillTrefId)
    {
        $thresholds = array();
        
        foreach ($this as $skillLevelThreshold) {
            if ($skillLevelThreshold->getImportSkillBaseId() != $importSkillBaseId) {
                continue;
            }
            
            if ($skillLevelThreshold->getImportSkillTrefId() != $importSkillTrefId) {
                continue;
            }
            
            $thresholds[] = $skillLevelThreshold;
        }
        
        return $thresholds;
    }
    
    /**
     * @return ilTestSkillLevelThresholdImport
     */
    public function current()
    {
        return current($this->importedSkillLevelThresholds);
    }
    
    /**
     * @return ilTestSkillLevelThresholdImport
     */
    public function next()
    {
        return next($this->importedSkillLevelThresholds);
    }
    
    /**
     * @return integer|bool
     */
    public function key()
    {
        return key($this->importedSkillLevelThresholds);
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->importedSkillLevelThresholds) !== null;
    }
    
    /**
     * @return ilTestSkillLevelThresholdImport|bool
     */
    public function rewind()
    {
        return reset($this->importedSkillLevelThresholds);
    }
}
