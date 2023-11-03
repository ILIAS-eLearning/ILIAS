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
 * @classDescription Table presentation of course/group relevant user data fields
 * @author           Stefan Meyer <meyer@leifos.com>
 */
class ilObjectCustomUserFieldsTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->setTitle(
            $this->lng->txt(ilObject::_lookupType($this->getParentObject()->getObjId()) . '_custom_user_fields')
        );

        $this->addColumn('', '', "1");
        $this->addColumn($this->lng->txt('ps_cdf_name'), 'name', '30%');
        $this->addColumn($this->lng->txt('ps_cdf_type'), 'type', '30%');
        $this->addColumn($this->lng->txt('ps_cdf_required'), '', '20%');
        $this->addColumn('', '', '20%');

        $this->setDefaultOrderField('name');
        $this->setDefaultOrderDirection('asc');

        $this->addMultiCommand('confirmDeleteFields', $this->lng->txt('delete'));
        $this->addCommandButton('saveFields', $this->lng->txt('save'));

        $this->setSelectAllCheckbox('field_ids[]');

        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->enable('select_all');

        $this->setRowTemplate('tpl.mem_cust_user_data_table_row.html', 'Services/Membership');
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['field_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['name']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('REQUIRED_CHECKED', $a_set['required'] ? 'checked="checked"' : '');

        $this->ctrl->setParameter($this->getParentObject(), 'field_id', $a_set['field_id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editField'));
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
    }

    /**
     * @param ilCourseDefinedFieldDefinition[] $a_defs
     */
    public function parse(array $a_defs): void
    {
        $rows = [];
        foreach ($a_defs as $def) {
            $rows[$def->getId()]['field_id'] = $def->getId();
            $rows[$def->getId()]['name'] = $def->getName();

            switch ($def->getType()) {
                case ilCourseDefinedFieldDefinition::IL_CDF_TYPE_SELECT:
                    $rows[$def->getId()]['type'] = $this->lng->txt('ps_type_select');
                    break;

                case ilCourseDefinedFieldDefinition::IL_CDF_TYPE_TEXT:
                    $rows[$def->getId()]['type'] = $this->lng->txt('ps_type_text');
                    break;
            }

            $rows[$def->getId()]['required'] = $def->isRequired();
        }
        $this->setData($rows);
        if (!count($rows)) {
            $this->clearCommandButtons();
        }
    }
}
