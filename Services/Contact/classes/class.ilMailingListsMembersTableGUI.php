<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailingListsMembersTableGUI extends ilTable2GUI
{
    public function __construct(ilMailingListsGUI $a_parent_obj, string $a_parent_cmd, ilMailingList $mailing_list)
    {
        $this->setId('show_mlng_mmbrs_list_tbl_' . $mailing_list->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'showMemberForm'));
        $this->setTitle($this->lng->txt('mail_members_of_mailing_list') . ' ' . $mailing_list->getTitle());
        $this->setRowTemplate('tpl.mail_mailing_lists_membersrow.html', 'Services/Contact');

        $this->addCommandButton('showMailingLists', $this->lng->txt('back'));

        $this->setDefaultOrderField('title');

        $this->initColumns();
    }

    protected function initColumns() : void
    {
        $this->addColumn('', 'check', '1%', true);
        $this->addColumn($this->lng->txt('user'), 'user', '99%');
    }
}
