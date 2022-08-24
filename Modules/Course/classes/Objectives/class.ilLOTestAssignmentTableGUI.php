<?php

declare(strict_types=0);
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
 * Class ilLOTestAssignmentTableGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOTestAssignmentTableGUI extends ilTable2GUI
{
    public const TYPE_MULTIPLE_ASSIGNMENTS = 1;
    public const TYPE_SINGLE_ASSIGNMENTS = 2;

    private int $test_type = 0;
    private int $assignment_type = self::TYPE_SINGLE_ASSIGNMENTS;
    private ilLOSettings $settings;
    private int $container_id = 0;

    protected ilDBInterface $db;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_container_id,
        int $a_test_type,
        int $a_assignment_type = self::TYPE_SINGLE_ASSIGNMENTS
    ) {
        global $DIC;

        $this->test_type = $a_test_type;
        $this->assignment_type = $a_assignment_type;
        $this->container_id = $a_container_id;
        $this->db = $DIC->database();

        $this->setId('obj_loc_' . $a_container_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->settings = ilLOSettings::getInstanceByObjId($a_container_id);
        $this->initTitle();
        $this->setTopCommands(false);
    }

    public function initTitle(): void
    {
        switch ($this->test_type) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                if ($this->getAssignmentType() == self::TYPE_SINGLE_ASSIGNMENTS) {
                    if ($this->getSettings()->isInitialTestQualifying()) {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_its_q_all'));
                    } else {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_its_nq_all'));
                    }
                } elseif ($this->getSettings()->isInitialTestQualifying()) {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_it_q'));
                } else {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_it_nq'));
                }
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                if ($this->getAssignmentType() == self::TYPE_SINGLE_ASSIGNMENTS) {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_qts_all'));
                } else {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_qt'));
                }
                break;
        }
    }

    public function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    public function getAssignmentType(): int
    {
        return $this->assignment_type;
    }

    /**
     * Init table
     */
    public function init(): void
    {
        $this->addColumn('', '', '20px');
        $this->addColumn($this->lng->txt('title'), 'title');

        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->addColumn($this->lng->txt('crs_objectives'), 'objective');
        }

        $this->addColumn($this->lng->txt('crs_loc_tbl_tst_type'), 'ttype');
        $this->addColumn($this->lng->txt('crs_loc_tbl_tst_qst_qpl'), 'qstqpl');

        $this->setRowTemplate("tpl.crs_loc_tst_row.html", "Modules/Course");
        $this->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this->getParentObject()));

        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->addMultiCommand('confirmDeleteTests', $this->lng->txt('crs_loc_delete_assignment'));
            $this->setDefaultOrderField('objective');
            $this->setDefaultOrderDirection('asc');
        } else {
            $this->addMultiCommand('confirmDeleteTest', $this->lng->txt('crs_loc_delete_assignment'));
            $this->setDefaultOrderField('title');
            $this->setDefaultOrderDirection('asc');
        }
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->tpl->setVariable('VAL_ID', $a_set['assignment_id']);
        } else {
            $this->tpl->setVariable('VAL_ID', $a_set['ref_id']);
        }
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);

        $this->ctrl->setParameterByClass('ilobjtestgui', 'ref_id', $a_set['ref_id']);
        $this->ctrl->setParameterByClass('ilobjtestgui', 'cmd', 'questionsTabGateway');
        $this->tpl->setVariable(
            'TITLE_LINK',
            $this->ctrl->getLinkTargetByClass('ilobjtestgui')
        );

        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->tpl->setCurrentBlock('objectives');
            $this->tpl->setVariable('VAL_OBJECTIVE', (string) $a_set['objective']);
            $this->tpl->parseCurrentBlock();
        }

        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }

        $type = '';
        switch ($a_set['ttype']) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                $type = $this->lng->txt('tst_question_set_type_fixed');
                break;

            case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                $type = $this->lng->txt('tst_question_set_type_random');
                break;
        }

        $this->tpl->setVariable('VAL_TTYPE', $type);
        $this->tpl->setVariable('VAL_QST_QPL', $a_set['qst_info']);

        if (isset($a_set['qpls']) && is_array($a_set['qpls']) && $a_set['qpls'] !== []) {
            foreach ($a_set['qpls'] as $title) {
                $this->tpl->setCurrentBlock('qpl');
                $this->tpl->setVariable('MAT_TITLE', $title);
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->touchBlock('ul_begin');
            $this->tpl->touchBlock('ul_end');
        }
    }

    public function parseMultipleAssignments(): void
    {
        $assignments = ilLOTestAssignments::getInstance($this->container_id);
        $available = $assignments->getAssignmentsByType($this->test_type);
        $data = array();
        foreach ($available as $assignment) {
            try {
                $tmp = $this->doParse($assignment->getTestRefId(), $assignment->getObjectiveId());
            } catch (ilLOInvalidConfigurationException $e) {
                $assignment->delete();
                continue;
            }
            if ($tmp !== []) {
                // add assignment id
                $tmp['assignment_id'] = $assignment->getAssignmentId();
                $data[] = $tmp;
            }
        }

        $this->setData($data);
    }

    public function parse(int $a_tst_ref_id): void
    {
        $this->setData(array($this->doParse($a_tst_ref_id)));
    }

    /**
     * Parse test
     * throws ilLOInvalidConfigurationException in case assigned test cannot be found.
     */
    protected function doParse(int $a_tst_ref_id, int $a_objective_id = 0): array
    {
        $tst = ilObjectFactory::getInstanceByRefId($a_tst_ref_id, false);

        if (!$tst instanceof ilObjTest) {
            throw new ilLOInvalidConfigurationException('No valid test given');
        }
        $tst_data['ref_id'] = $tst->getRefId();
        $tst_data['title'] = $tst->getTitle();
        $tst_data['description'] = $tst->getLongDescription();
        $tst_data['ttype'] = $tst->getQuestionSetType();

        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $tst_data['objective'] = ilCourseObjective::lookupObjectiveTitle($a_objective_id);
        }

        switch ($tst->getQuestionSetType()) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                $tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_num_qst');
                $tst_data['qst_info'] .= (' ' . count($tst->getAllQuestions()));
                break;

            default:
                // get available assiged question pools

                $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
                    $GLOBALS['DIC']['ilDB'],
                    $tst,
                    new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                        $GLOBALS['DIC']['ilDB'],
                        $tst
                    )
                );

                $list->loadDefinitions();

                // tax translations
                $translater = new ilTestTaxonomyFilterLabelTranslater($this->db);
                $translater->loadLabels($list);

                $tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_qpls');
                $num = 0;
                foreach ($list as $definition) {
                    /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
                    $title = $definition->getPoolTitle();
                    $filterTitle = array();
                    $filterTitle[] = $translater->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter());
                    $filterTitle[] = $translater->getTypeFilterLabel($definition->getTypeFilter());
                    if (!empty($filterTitle)) {
                        $title .= ' -> ' . implode(' / ', $filterTitle);
                    }
                    $tst_data['qpls'][] = $title;
                    ++$num;
                }
                if ($num === 0) {
                    $tst_data['qst_info'] .= (' ' . 0);
                }
                break;
        }
        return $tst_data;
    }
}
