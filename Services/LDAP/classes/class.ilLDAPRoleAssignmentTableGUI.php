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
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPRoleAssignmentTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('ldap_rule_type'), 'type', "20%");
        $this->addColumn($this->lng->txt('ldap_ilias_role'), 'role', "30%");
        $this->addColumn($this->lng->txt('ldap_rule_condition'), 'condition', "20%");
        $this->addColumn($this->lng->txt('ldap_add_remove'), '', '30%');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_role_assignment_row.html", "Services/LDAP");
        $this->setDefaultOrderField('type');
        $this->setDefaultOrderDirection("desc");
    }

    /** @noinspection DuplicatedCode */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('VAL_CONDITION', $a_set['condition']);
        $this->tpl->setVariable('VAL_ROLE', $a_set['role']);
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));

        if ($a_set['add']) {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('no'));
        }
        if ($a_set['remove']) {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('no'));
        }


        $this->ctrl->setParameter($this->getParentObject(), 'rule_id', $a_set['id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editRoleAssignment'));
    }

    /**
     * Parse
     *
     * @param array array of LDAPRoleAssignmentRule
     *
     */
    public function parse($rule_objs): void
    {
        $records_arr = [];
        foreach ($rule_objs as $rule) {
            $tmp_arr['id'] = $rule->getRuleId();

            switch ($rule->getType()) {
                case ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_attribute');
                    break;
                case ilLDAPRoleAssignmentRule::TYPE_GROUP:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_group');
                    break;
                case ilLDAPRoleAssignmentRule::TYPE_PLUGIN:
                    $tmp_arr['type'] = $this->lng->txt('ldap_role_by_plugin');
                    break;

            }

            $tmp_arr['condition'] = $rule->conditionToString();
            $tmp_arr['add'] = $rule->isAddOnUpdateEnabled();
            $tmp_arr['remove'] = $rule->isRemoveOnUpdateEnabled();

            $tmp_arr['role'] = ilObject::_lookupTitle($rule->getRoleId());

            $records_arr[] = $tmp_arr;
        }

        $this->setData($records_arr);
    }
}
