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

declare(strict_types=1);

/**
 * Class ilLTIConsumerGradeSynchronizationTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationTableGUI extends ilTable2GUI
{
    public const TABLE_ID = 'lti_grade_table';

    protected bool $isMultiActorReport;
    protected array $filter = [];
    private \ILIAS\DI\Container $dic;
    private ilLanguage $language;

    /**
     * @throws ilCtrlException
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd, bool $isMultiActorReport)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->dic = $DIC;
        $this->language = $DIC->language();

        $this->isMultiActorReport = $isMultiActorReport;

        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $DIC->language()->loadLanguageModule('form');

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.lti_grade_synchronization_table_row.html', 'Modules/LTIConsumer');

        $this->initColumns();
        $this->initFilter();

        $this->setExternalSegmentation(false);
        $this->setExternalSorting(true);

        $this->setDefaultOrderField('lti_timestamp');
        $this->setDefaultOrderDirection('desc');
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->language->txt('tbl_grade_date'), 'lti_timestamp');

        if ($this->isMultiActorReport) {
            $this->addColumn($this->language->txt('tbl_grade_actor'), 'actor');
        }
        $this->addColumn($this->language->txt('tbl_grade_score'), 'score_given');
        $this->addColumn($this->language->txt('tbl_grade_activity_progress'), '');
        $this->addColumn($this->language->txt('tbl_grade_grading_progress'), '');
        $this->addColumn($this->language->txt('tbl_grade_stored'), '');
    }

    public function initFilter(): void
    {
        if ($this->isMultiActorReport) {
            $ti = new ilTextInputGUI($this->language->txt('tbl_grade_actor'), "actor");
            $ti->setDataSource($this->dic->ctrl()->getLinkTarget($this->parent_obj, 'asyncUserAutocomplete', '', true));
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["actor"] = $ti->getValue();
        }

        $options = array(
            '' => $this->language->txt('grade_activity_progress_all'),
            'Initialized' => $this->language->txt('grade_activity_progress_initialized'),
            'Started' => $this->language->txt('grade_activity_progress_started'),
            'InProgress' => $this->language->txt('grade_activity_progress_inprogress'),
            'Submitted' => $this->language->txt('grade_activity_progress_submitted'),
            'Completed' => $this->language->txt('grade_activity_progress_completed')
        );

        $si = new ilSelectInputGUI($this->language->txt('tbl_grade_activity_progress'), "activity_progress");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["activity_progress"] = $si->getValue();

        $options = array(
            '' => $this->language->txt('grade_grading_progress_all'),
            'NotReady' => $this->language->txt('grade_grading_progress_notready'),
            'Failed' => $this->language->txt('grade_grading_progress_failed'),
            'Pending' => $this->language->txt('grade_grading_progress_pending'),
            'PendingManual' => $this->language->txt('grade_grading_progress_pendingmanual'),
            'FullyGraded' => $this->language->txt('grade_grading_progress_fullygraded')
        );

        $si = new ilSelectInputGUI($this->language->txt('tbl_grade_grading_progress'), "grading_progress");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["grading_progress"] = $si->getValue();

        $dp = new ilDateDurationInputGUI($this->language->txt('tbl_grade_period'), 'period');
        $dp->setShowTime(true);
        $this->addFilterItem($dp);
        $dp->readFromSession();
        $this->filter["period"] = $dp->getValue();
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('STMT_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set['lti_timestamp'], IL_CAL_DATETIME)));
        if ($this->isMultiActorReport) {
            $this->tpl->setVariable('STMT_ACTOR', $a_set['actor']);
        }
        $this->tpl->setVariable('STMT_SCORE', $a_set['score_given'] . ' / ' . $a_set['score_maximum']);
        $this->tpl->setVariable('STMT_ACTIVITY_PROGRESS', $this->language->txt('grade_activity_progress_' . strtolower($a_set['activity_progress'])));
        $this->tpl->setVariable('STMT_GRADING_PROGRESS', $this->language->txt('grade_grading_progress_' . strtolower($a_set['grading_progress'])));
        $this->tpl->setVariable('STMT_STORED', ilDatePresentation::formatDate(new ilDateTime($a_set['stored'], IL_CAL_DATETIME)));
    }

}
