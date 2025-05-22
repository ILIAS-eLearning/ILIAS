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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarSharedRoleListTableGUI extends ilTable2GUI
{
    protected ilRbacReview $rbacreview;
    protected array $role_ids = array();

    public function __construct(object $parent_obj, string $parent_cmd)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();

        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.calendar_shared_role_list_row.html', 'components/ILIAS/Calendar');
        $this->addColumn('', 'id', '1px');
        $this->addColumn($this->lng->txt('objs_role'), 'title', '75%');
        $this->addColumn($this->lng->txt('assigned_members'), 'num', '25%');

        $this->addMultiCommand('shareAssignRoles', $this->lng->txt('cal_share_cal'));
        $this->addMultiCommand('shareAssignRolesEditable', $this->lng->txt('cal_share_cal_editable'));
        $this->setSelectAllCheckbox('role_ids');
        $this->setPrefix('search');
    }

    public function setRoles(array $a_role_ids): void
    {
        $this->role_ids = $a_role_ids;
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        $this->tpl->setVariable('NUM_USERS', $a_set['num']);
    }

    public function parse(): void
    {
        $users = $roles = array();
        foreach ($this->role_ids as $id) {
            $tmp_data['title'] = ilObject::_lookupTitle($id);
            $tmp_data['description'] = ilObject::_lookupDescription($id);
            $tmp_data['id'] = $id;
            $tmp_data['num'] = count($this->rbacreview->assignedUsers($id));

            $roles[] = $tmp_data;
        }

        $this->setData($roles);
    }
}
