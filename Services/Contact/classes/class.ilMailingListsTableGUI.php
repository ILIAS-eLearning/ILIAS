<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailingListsTableGUI extends ilTable2GUI
{
    public function __construct(ilMailingListsGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->setId('show_mlng_lists_tbl');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'showForm'));
        $this->setTitle($this->lng->txt('mail_mailing_lists'));
        $this->setRowTemplate('tpl.mail_mailing_lists_listrow.html', 'Services/Contact');
        $this->setDefaultOrderField('title');
        $this->setSelectAllCheckbox('ml_id');
        $this->setNoEntriesText($this->lng->txt('mail_search_no'));

        $this->initColumns();
    }

    protected function initColumns() : void
    {
        $this->addColumn('', 'check', '10%', true);
        $this->addColumn($this->lng->txt('title'), 'title', '30%');
        $this->addColumn($this->lng->txt('description'), 'description', '30%');
        $this->addColumn($this->lng->txt('members'), 'members', '20%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');
    }
}
