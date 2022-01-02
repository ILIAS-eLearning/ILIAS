<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumStatisticsTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesForum
 */
class ilForumStatisticsTableGUI extends ilTable2GUI
{
    private bool $hasActiveLp = false;
    /** @var int[] */
    private array $completed = [];
    /** @var int[] */
    private array $failed = [];
    /** @var int[] */
    private array $in_progress = [];

    public function __construct(ilObjForumGUI $a_parent_obj, string $a_parent_cmd, ilObjForum $forum)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lp = ilObjectLP::getInstance($forum->getId());
        if ($lp->isActive()) {
            $this->hasActiveLp = true;
        }

        $this->setRowTemplate('tpl.statistics_table_row.html', 'Modules/Forum');

        $columns = $this->getColumnDefinition();
        foreach ($columns as $index => $column) {
            $this->addColumn(
                $column['txt'],
                isset($column['sortable']) && $column['sortable'] ? $column['field'] : '',
                ((string) ceil((100 / count($columns)))) . '%s'
            );
        }

        if ($this->hasActiveLp) {
            $this->lng->loadLanguageModule('trac');
            $this->completed = ilLPStatusWrapper::_lookupCompletedForObject($forum->getId());
            $this->in_progress = ilLPStatusWrapper::_lookupInProgressForObject($forum->getId());
            $this->failed = ilLPStatusWrapper::_lookupFailedForObject($forum->getId());
        }

        $this->setDefaultOrderField('ranking');
        $this->setDefaultOrderDirection('desc');

        $this->enable('hits');
        $this->enable('sort');
    }

    /**
     * @return array<int, array{field: string, txt: string, sortable: bool}>
     */
    protected function getColumnDefinition() : array
    {
        $i = 0;

        $columns = [];

        $columns[++$i] = [
            'field' => 'ranking',
            'txt' => $this->lng->txt('frm_statistics_ranking'),
            'sortable' => true,
        ];
        $columns[++$i] = [
            'field' => 'login',
            'txt' => $this->lng->txt('login'),
            'sortable' => true,
        ];
        $columns[++$i] = [
            'field' => 'lastname',
            'txt' => $this->lng->txt('lastname'),
            'sortable' => true,
        ];
        $columns[++$i] = [
            'field' => 'firstname',
            'txt' => $this->lng->txt('firstname'),
            'sortable' => true,
        ];
        if ($this->hasActiveLp) {
            $columns[++$i] = [
                'field' => 'progress',
                'txt' => $this->lng->txt('learning_progress'),
                'sortable' => false,
            ];
        }

        return $columns;
    }

    protected function fillRow(array $a_set) : void
    {
        parent::fillRow($a_set);

        if ($this->hasActiveLp) {
            $this->tpl->setCurrentBlock('val_lp');
            switch (true) {
                case in_array($a_set['usr_id'], $this->completed, false):
                    $this->tpl->setVariable('LP_STATUS_ALT', $this->lng->txt(ilLPStatus::LP_STATUS_COMPLETED));
                    $this->tpl->setVariable('LP_STATUS_PATH', ilUtil::getImagePath('scorm/complete.svg'));
                    break;

                case in_array($a_set['usr_id'], $this->in_progress, false):
                    $this->tpl->setVariable('LP_STATUS_ALT', $this->lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS));
                    $this->tpl->setVariable('LP_STATUS_PATH', ilUtil::getImagePath('scorm/incomplete.svg'));
                    break;

                case in_array($a_set['usr_id'], $this->failed, false):
                    $this->tpl->setVariable('LP_STATUS_ALT', $this->lng->txt(ilLPStatus::LP_STATUS_FAILED));
                    $this->tpl->setVariable('LP_STATUS_PATH', ilUtil::getImagePath('scorm/failed.svg'));
                    break;

                default:
                    $this->tpl->setVariable('LP_STATUS_ALT', $this->lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED));
                    $this->tpl->setVariable('LP_STATUS_PATH', ilUtil::getImagePath('scorm/not_attempted.svg'));
                    break;
            }
            $this->tpl->parseCurrentBlock();
        }
    }

    public function numericOrdering(string $a_field) : bool
    {
        switch ($a_field) {
            case 'ranking':
                return true;

            default:
                return false;
        }
    }
}
