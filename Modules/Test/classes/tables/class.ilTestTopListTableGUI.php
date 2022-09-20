<?php

declare(strict_types=1);

/**
 * Class ilTestTopListTableGUI
 */
class ilTestTopListTableGUI extends ilTable2GUI
{
    private ilObjTest $test;

    public function __construct(ilTestToplistGUI $a_parent_obj, ilObjTest $test)
    {
        $this->test = $test;

        $this->setId('tst_top_list_' . $this->test->getRefId());
        parent::__construct($a_parent_obj, '', '');

        $this->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');

        $this->setEnableNumInfo(false);
        $this->disable('sort');
        $this->setLimit((int) $this->test->getHighscoreTopNum());

        $this->buildColumns();
    }

    private function buildColumns(): void
    {
        $this->addColumn($this->lng->txt('toplist_col_rank'));
        $this->addColumn($this->lng->txt('toplist_col_participant'));

        if ($this->test->getHighscoreAchievedTS()) {
            $this->addColumn($this->lng->txt('toplist_col_achieved'));
        }

        if ($this->test->getHighscoreScore()) {
            $this->addColumn($this->lng->txt('toplist_col_score'));
        }

        if ($this->test->getHighscorePercentage()) {
            $this->addColumn($this->lng->txt('toplist_col_percentage'));
        }

        if ($this->test->getHighscoreHints()) {
            $this->addColumn($this->lng->txt('toplist_col_hints'));
        }

        if ($this->test->getHighscoreWTime()) {
            $this->addColumn($this->lng->txt('toplist_col_wtime'));
        }
    }

    protected function fillRow(array $a_set): void
    {
        $rowHighlightClass = '';

        if ($a_set['is_actor']) {
            $rowHighlightClass = 'tblrowmarked';
        }
        $this->tpl->setVariable('VAL_HIGHLIGHT', $rowHighlightClass);

        $this->tpl->setVariable('VAL_RANK', (string) $a_set['rank']);
        $this->tpl->setVariable('VAL_PARTICIPANT', (string) $a_set['participant']);

        if ($this->test->getHighscoreAchievedTS()) {
            $this->tpl->setVariable('VAL_ACHIEVED', (string) ilDatePresentation::formatDate($a_set['achieved']));
        }

        if ($this->test->getHighscoreScore()) {
            $this->tpl->setVariable('VAL_SCORE', (string) $a_set['score']);
        }

        if ($this->test->getHighscorePercentage()) {
            $this->tpl->setVariable('VAL_PERCENTAGE', (string) $a_set['percentage']);
        }

        if ($this->test->getHighscoreHints()) {
            $this->tpl->setVariable('VAL_HINTS', (string) $a_set['hints']);
        }

        if ($this->test->getHighscoreWTime()) {
            $this->tpl->setVariable('VAL_TIME', (string) $a_set['time']);
        }
    }
}
