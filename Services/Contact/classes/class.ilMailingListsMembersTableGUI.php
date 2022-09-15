<?php

declare(strict_types=1);

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
        $this->setTitle(sprintf($this->lng->txt('mail_members_of_mailing_list'), $mailing_list->getTitle()));
        $this->setRowTemplate('tpl.mail_mailing_lists_membersrow.html', 'Services/Contact');

        $this->addCommandButton('showMailingLists', $this->lng->txt('back'));

        $this->setDefaultOrderField('title');

        $this->initColumns();
    }

    protected function initColumns(): void
    {
        $this->addColumn('', 'check', '1%', true);
        $this->addColumn($this->lng->txt('user'), 'user', '99%');
    }
}
