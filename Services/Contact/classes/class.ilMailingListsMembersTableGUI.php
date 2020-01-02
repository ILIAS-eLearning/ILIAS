<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailingListsMembersTableGUI extends ilTable2GUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param                $a_parent_obj
     * @param string         $a_parent_cmd
     * @param ilMailingList  $mailing_list
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', ilMailingList $mailing_list)
    {
        global $DIC;

        $this->lng  = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];

        $this->setId('show_mlng_mmbrs_list_tbl_' . $mailing_list->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj), 'showMemberForm');
        $this->setTitle($this->lng->txt('mail_members_of_mailing_list') . ' ' . $mailing_list->getTitle());
        $this->setRowTemplate('tpl.mail_mailing_lists_membersrow.html', 'Services/Contact');

        $this->addCommandButton('showMailingLists', $this->lng->txt('back'));

        $this->setDefaultOrderField('title');

        $this->initColumns();
    }

    protected function initColumns()
    {
        $this->addColumn('', 'check', '1%', true);
        $this->addColumn($this->lng->txt('user'), 'user', '99%');
    }
}
