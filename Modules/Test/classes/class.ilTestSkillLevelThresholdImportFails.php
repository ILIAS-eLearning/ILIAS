<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportFails
{
    /**
     * @var ilSetting
     */
    protected $settings;
    
    /**
     * @var integer
     */
    protected $parentObjId;
    
    /**
     * ilTestSkillLevelThresholdImportFails constructor.
     * @param $parentObjId
     */
    public function __construct($parentObjId)
    {
        $this->parentObjId = $parentObjId;
    }
    
    /**
     * @return ilSetting
     */
    protected function getSettings()
    {
        if ($this->settings === null) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionAssignedSkillList.php';
            
            $this->settings = new ilSetting('assimportfails');
        }
        
        return $this->settings;
    }
    
    /**
     * @return int
     */
    protected function getParentObjId()
    {
        return $this->parentObjId;
    }
    
    /**
     * @return string
     */
    protected function buildSettingsKey()
    {
        return 'failed_imp_slt_parentobj_' . $this->getParentObjId();
    }
    
    /**
     * @return ilAssQuestionAssignedSkillList|null
     */
    public function getFailedImports()
    {
        $value = $this->getSettings()->get($this->buildSettingsKey(), null);
        
        if ($value !== null) {
            return unserialize($value);
        }
        
        return null;
    }
    
    /**
     * @param ilAssQuestionAssignedSkillList $skillList
     */
    public function registerFailedImports(ilAssQuestionAssignedSkillList $skillList)
    {
        $this->getSettings()->set($this->buildSettingsKey(), serialize($skillList));
    }
    
    /**
     */
    public function deleteRegisteredImportFails()
    {
        $this->getSettings()->delete($this->buildSettingsKey());
    }
    
    /**
     * @return bool
     */
    public function failedImportsRegistered()
    {
        return $this->getFailedImports() !== null;
    }
    
    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFailedImportsMessage(ilLanguage $lng)
    {
        require_once 'Services/Skill/classes/class.ilBasicSkill.php';
        
        $msg = $lng->txt('tst_failed_imp_skl_thresholds');
        
        $msg .= '<ul>';
        foreach ($this->getFailedImports() as $skillKey) {
            list($skillBaseId, $skillTrefId) = explode(':', $skillKey);
            $skillTitle = ilBasicSkill::_lookupTitle($skillBaseId, $skillTrefId);
            
            $msg .= '<li>' . $skillTitle . '</li>';
        }
        $msg .= '</ul>';
        
        return $msg;
    }
}
