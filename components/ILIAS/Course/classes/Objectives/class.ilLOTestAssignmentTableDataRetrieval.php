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

declare(strict_types=0);

use ILIAS\UI\Component\Table\DataRetrieval as ilTableDataRetrievalInterface;
use ILIAS\DI\UIServices as ilUIServices;

class ilLOTestAssignmentTableDataRetrieval implements ilTableDataRetrievalInterface
{
    protected array $data;

    public function __construct(
        protected ilLanguage $lng,
        protected ilDBInterface $db,
        protected ilUIServices $ui_services,
        protected ilCtrl $ctrl,
        protected int $assignment_type
    ) {
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        if ($column_name === ilLOTestAssignmentTableGUI::TABLE_COL_TITLE) {
            uasort($this->data, function ($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
        }
        if ($direction === 'DESC') {
            $rows = array_reverse($this->data);
        } else {
            $rows = $this->data;
        }
        foreach ($rows as $row) {
            $id = $this->assignment_type === ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS
                ? $row['assignment_id']
                : $row['ref_id'];
            $type = '';
            switch ($row['ttype']) {
                case ilObjTest::QUESTION_SET_TYPE_FIXED:
                    $type = $this->lng->txt('tst_question_set_type_fixed');
                    break;

                case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                    $type = $this->lng->txt('tst_question_set_type_random');
                    break;
            }
            $this->ctrl->setParameterByClass('ilobjtestgui', 'ref_id', $row['ref_id']);
            $this->ctrl->setParameterByClass('ilobjtestgui', 'cmd', 'questionsTabGateway');
            $title_link = $this->ui_services->factory()->link()->standard($row['title'], $this->ctrl->getLinkTargetByClass('ilobjtestgui'));
            $data = [];
            $data[ilLOTestAssignmentTableGUI::TABLE_COL_TITLE] = $title_link;
            if ($this->assignment_type === ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS) {
                $data[ilLOTestAssignmentTableGUI::TABLE_COL_COURSE_OBJECTIVES] = $row['objective'];
            }
            $data[ilLOTestAssignmentTableGUI::TABLE_COL_SELECTION_OF_TEST_QUESTIONS] = $type;
            $data[ilLOTestAssignmentTableGUI::TABLE_COL_QESTIONS] = $row['qst_info'];

            if (isset($row['qpls']) && count($row['qpls']) > 0) {
                $data[ilLOTestAssignmentTableGUI::TABLE_COL_QESTIONS] .= '<br>' . implode('<br>', $row['qpls']);
            }

            yield $row_builder->buildDataRow(
                $id . '',
                $data
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->data);
    }

    protected function doParse(
        int $a_tst_ref_id,
        int $a_objective_id = 0
    ): array {
        $tst = ilObjectFactory::getInstanceByRefId($a_tst_ref_id, false);
        if (!$tst instanceof ilObjTest) {
            throw new ilLOInvalidConfigurationException('No valid test given');
        }
        $tst_data['ref_id'] = $tst->getRefId();
        $tst_data['title'] = $tst->getTitle();
        $tst_data['description'] = $tst->getLongDescription();
        $tst_data['ttype'] = $tst->getQuestionSetType();
        if ($this->assignment_type == ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS) {
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
                $translator = new ilTestQuestionFilterLabelTranslator($this->db, $this->lng);
                $translator->loadLabels($list);
                $tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_qpls');
                $num = 0;
                foreach ($list as $definition) {
                    $title = $definition->getPoolTitle();
                    $filterTitle = [];
                    $filterTitle[] = $translator->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter());
                    $filterTitle[] = $translator->getTypeFilterLabel($definition->getTypeFilter());
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

    public function parseSingleAssignment(
        int $a_tst_ref_id,
        int $a_objective_id = 0
    ): void {
        $this->data = [$this->doParse($a_tst_ref_id, $a_objective_id)];
    }

    public function parseMultipleAssignments(
        int $container_id,
        int $test_type
    ): void {
        $assignments = ilLOTestAssignments::getInstance($container_id);
        $available = $assignments->getAssignmentsByType($test_type);
        $data = [];
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
        $this->data = $data;
    }

    public function allIds(): array
    {
        $ids = [];
        foreach ($this->data as $assignment) {
            $id = $this->assignment_type === ilLOTestAssignmentTableGUI::TYPE_MULTIPLE_ASSIGNMENTS
                ? $assignment['assignment_id']
                : $assignment['ref_id'];
            $ids[] = $id;
        }
        return $ids;
    }
}
