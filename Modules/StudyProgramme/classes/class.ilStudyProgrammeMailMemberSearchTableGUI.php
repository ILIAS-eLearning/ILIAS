<?php declare(strict_types=1);

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilStudyProgrammeMailMemberSearchTableGUI extends ilTable2GUI
{
    /**
     * @inheritdoc
     */
    public function __construct(ilStudyProgrammeMailMemberSearchGUI $parent_obj, int $root_obj_id, string $parent_cmd = "")
    {
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];

        $this->setId('mmsearch_' . $root_obj_id);
        parent::__construct($parent_obj, $parent_cmd);
        $this->lng->loadLanguageModule('prg');
        $this->setTitle($this->lng->txt('members'));

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->ctrl->clearParameters($parent_obj);

        $this->setRowTemplate('tpl.mail_member_search_row.html', 'Modules/StudyProgramme');

        // setup columns
        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), 'login', '22%');
        $this->addColumn($this->lng->txt('name'), 'name', '22%');

        $this->setSelectAllCheckbox('user_ids[]');
        $this->setShowRowsSelector(true);

        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mail_assignments'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    /**
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
    }
}
