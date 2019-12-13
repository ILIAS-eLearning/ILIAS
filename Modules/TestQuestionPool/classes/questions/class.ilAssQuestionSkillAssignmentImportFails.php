<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImportFails
{
    /**
     * @var ilAssQuestionSkillAssignmentRegistry
     */
    protected $settings;
    
    /**
     * @var integer
     */
    protected $parentObjId;
    
    /**
     * ilAssQuestionSkillAssignmentImportFails constructor.
     * @param $parentObjId
     */
    public function __construct($parentObjId)
    {
        $this->parentObjId = $parentObjId;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentRegistry
     */
    protected function getSettings()
    {
        if ($this->settings === null) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportList.php';
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentRegistry.php';
            $this->settings = new ilAssQuestionSkillAssignmentRegistry(new ilSetting('assimportfails'));
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
        return 'failed_imp_qsa_parentobj_' . $this->getParentObjId();
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentImportList|null
     */
    public function getFailedImports()
    {
        $value = $this->getSettings()->getStringifiedImports($this->buildSettingsKey(), null);

        if ($value !== null) {
            return unserialize($value);
        }

        return null;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentImportList $assignmentList
     */
    public function registerFailedImports(ilAssQuestionSkillAssignmentImportList $assignmentList)
    {
        $this->getSettings()->setStringifiedImports($this->buildSettingsKey(), serialize($assignmentList));
    }
    
    /**
     */
    public function deleteRegisteredImportFails()
    {
        $this->getSettings()->deleteStringifiedImports($this->buildSettingsKey());
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
        $handledSkills = array();
        $msg = $lng->txt('tst_failed_imp_qst_skl_assign');
        
        $msg .= '<ul>';
        foreach ($this->getFailedImports() as $assignmentImport) {
            $sklBaseId = $assignmentImport->getImportSkillBaseId();
            $sklTrefId = $assignmentImport->getImportSkillTrefId();
            
            if (isset($handledSkills["$sklBaseId:$sklTrefId"])) {
                continue;
            }
            
            $handledSkills["$sklBaseId:$sklTrefId"] = true;
            
            $msg .= '<li>' . $assignmentImport->getImportSkillTitle() . '</li>';
        }
        $msg .= '</ul>';
        
        return $msg;
    }
}
