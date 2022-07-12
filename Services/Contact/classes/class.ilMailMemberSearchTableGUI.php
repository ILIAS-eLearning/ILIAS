<?php declare(strict_types=1);

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
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailMemberSearchTableGUI extends ilTable2GUI
{
    public function __construct(ilMailMemberSearchGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $obj_id = ilObject::_lookupObjectId($a_parent_obj->ref_id);
        $this->setId('mmsearch_' . $obj_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('grp');
        $this->setTitle($this->lng->txt('members'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->ctrl->clearParameters($a_parent_obj);

        $this->setRowTemplate('tpl.mail_member_search_row.html', 'Services/Contact');

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), 'login', '22%');
        $this->addColumn($this->lng->txt('name'), 'name', '22%');
        $this->addColumn($this->lng->txt('role'), 'role', '22%');

        $this->setSelectAllCheckbox('user_ids[]');
        $this->setShowRowsSelector(true);
        
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mail_members'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    protected function fillRow(array $a_set) : void
    {
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
    }
}
