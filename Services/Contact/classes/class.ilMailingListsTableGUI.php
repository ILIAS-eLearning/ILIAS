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

    protected function initColumns(): void
    {
        $this->addColumn('', 'check', '1px', true);
        $this->addColumn($this->lng->txt('title'), 'title', '30%');
        $this->addColumn($this->lng->txt('description'), 'description', '40%');
        $this->addColumn($this->lng->txt('members'), 'members', '20%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');
    }
}
