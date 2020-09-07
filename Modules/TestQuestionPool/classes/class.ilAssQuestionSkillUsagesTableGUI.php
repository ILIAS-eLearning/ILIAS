<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillUsagesTableGUI extends ilTable2GUI
{
    const TABLE_ID = 'iaqsutg';
    
    const CMD_SHOW = 'show';

    /**
     * @var ilCtrl
     */
    private $myCtrl;

    /**
     * @var ilTemplate
     */
    private $myTpl;

    /**
     * @var ilLanguage
     */
    private $myLng;

    /**
     * @var ilDBInterface
     */
    private $myDb;
    
    /**
     * @param ilCtrl $myCtrl
     * @param ilTemplate $myTpl
     * @param ilLanguage $myLng
     * @param ilDBInterface $myDb
     * @param integer $poolId
     */
    public function __construct(ilCtrl $myCtrl, ilTemplate $myTpl, ilLanguage $myLng, ilDBInterface $myDb, $poolId)
    {
        $this->myCtrl = $myCtrl;
        $this->myTpl = $myTpl;
        $this->myLng = $myLng;
        $this->myDb = $myDb;
        
        $this->poolId = $poolId;
        
        $this->setId(self::TABLE_ID . $this->poolId);
        $this->setPrefix(self::TABLE_ID . $this->poolId);
        parent::__construct($this, self::CMD_SHOW);

        $this->setRowTemplate("tpl.il_as_qpl_skl_assign_stat_row.html", "Modules/TestQuestionPool");#

        $this->setDefaultOrderField("qpl_qst_skl_usg_skill_col");
        $this->setDefaultOrderDirection("asc");
    }

    public function executeCommand()
    {
        switch ($this->myCtrl->getNextClass()) {
            case strtolower(__CLASS__):
            case '':

                $command = $this->myCtrl->getCmd(self::CMD_SHOW) . 'Cmd';
                $this->$command();
                break;
            
            default:
                
                throw new ilTestQuestionPoolException('unsupported next class');
        }
    }
    
    private function showCmd()
    {
        $this->initColumns();
        
        
        $this->setData($this->buildTableRowsArray(
            $this->getUniqueAssignedSkillsStats()
        ));
        
        $this->myTpl->setContent($this->myCtrl->getHTML($this));
    }
    
    protected function initColumns()
    {
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_skill_col'), 'skill_title', '50%');
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_numq_col'), 'num_questions', '');
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_sklpnt_col'), 'max_skill_points', '');
    }
    
    public function fillRow($data)
    {
        $this->tpl->setVariable('SKILL_TITLE', $data['skill_title']);
        $this->tpl->setVariable('SKILL_PATH', $data['skill_path']);
        $this->tpl->setVariable('NUM_QUESTIONS', $data['num_questions']);
        $this->tpl->setVariable('MAX_SKILL_POINTS', $data['max_skill_points']);
    }
    
    public function numericOrdering($a_field)
    {
        switch ($a_field) {
            case 'num_questions':
            case 'max_skill_points':
                return true;
        }
        
        return false;
    }
    
    private function getUniqueAssignedSkillsStats()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->myDb);
        
        $assignmentList->setParentObjId($this->poolId);
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        return $assignmentList->getUniqueAssignedSkills();
    }
    
    private function buildTableRowsArray($assignedSkills)
    {
        $rows = array();
        
        foreach ($assignedSkills as $assignedSkill) {
            $rows[] = array(
                'skill_title' => $assignedSkill['skill_title'],
                'skill_path' => $assignedSkill['skill_path'],
                'num_questions' => $assignedSkill['num_assigns'],
                'max_skill_points' => $assignedSkill['max_points'],
            );
        }
        
        return $rows;
    }
}
