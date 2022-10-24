<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillUsagesTableGUI extends ilTable2GUI
{
    public const TABLE_ID = 'iaqsutg';

    public const CMD_SHOW = 'show';

    /**
     * @var ilCtrl
     */
    private $myCtrl;

    /**
     * @var ilGlobalTemplateInterface
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
    private int $poolId;

    /**
     * @param ilCtrl $myCtrl
     * @param ilGlobalTemplateInterface $myTpl
     * @param ilLanguage $myLng
     * @param ilDBInterface $myDb
     * @param integer $poolId
     */
    public function __construct(ilCtrl $myCtrl, ilGlobalTemplateInterface $myTpl, ilLanguage $myLng, ilDBInterface $myDb, $poolId)
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

    public function executeCommand(): bool
    {
        switch ($this->myCtrl->getNextClass()) {
            case strtolower(__CLASS__):
            case '':

                $command = $this->myCtrl->getCmd(self::CMD_SHOW) . 'Cmd';
                return (bool) $this->$command();
                break;

            default:

                throw new ilTestQuestionPoolException('unsupported next class');
        }
        return false;
    }

    private function showCmd(): void
    {
        $this->initColumns();


        $this->setData($this->buildTableRowsArray(
            $this->getUniqueAssignedSkillsStats()
        ));

        $this->myTpl->setContent($this->myCtrl->getHTML($this));
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_skill_col'), 'skill_title', '50%');
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_numq_col'), 'num_questions', '');
        $this->addColumn($this->myLng->txt('qpl_qst_skl_usg_sklpnt_col'), 'max_skill_points', '');
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('SKILL_TITLE', $a_set['skill_title']);
        $this->tpl->setVariable('SKILL_PATH', $a_set['skill_path']);
        $this->tpl->setVariable('NUM_QUESTIONS', $a_set['num_questions']);
        $this->tpl->setVariable('MAX_SKILL_POINTS', $a_set['max_skill_points']);
    }

    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'num_questions':
            case 'max_skill_points':
                return true;
        }

        return false;
    }

    private function getUniqueAssignedSkillsStats(): array
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->myDb);

        $assignmentList->setParentObjId($this->poolId);
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        return $assignmentList->getUniqueAssignedSkills();
    }

    private function buildTableRowsArray($assignedSkills): array
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
