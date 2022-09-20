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
 */


/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Table presentation of conditions
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConditionHandlerTableGUI extends ilTable2GUI
{
    protected bool $enable_editing;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, bool $a_enable_editing = false)
    {
        $this->enable_editing = $a_enable_editing;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initTable();
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('OBJ_SRC', $a_set['icon']);
        $this->tpl->setVariable('OBJ_ALT', $a_set['icon_alt']);
        $this->tpl->setVariable('OBJ_TITLE', $a_set['title']);
        $this->tpl->setVariable('OBJ_LINK', ilLink::_getLink($a_set['ref_id']));
        $this->tpl->setVariable('OBJ_DESCRIPTION', $a_set['description']);
        $this->tpl->setVariable('COND_ID', $a_set['id']);
        $this->tpl->setVariable('OBJ_CONDITION', $a_set['condition']);

        if (!$this->enable_editing) {
            $this->tpl->setCurrentBlock("obligatory_static");
            $this->tpl->setVariable(
                'OBL_SRC',
                ilUtil::getImagePath($a_set['obligatory'] ? 'icon_ok.svg' : 'icon_not_ok.svg')
            );
            $this->tpl->setVariable(
                'OBL_ALT',
                $this->lng->txt($a_set['obligatory'] ?
                    'precondition_obligatory_alt' :
                    'precondition_not_obligatory_alt')
            );
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("obligatory_edit");
            $this->tpl->setVariable('OBL_ID', $a_set['id']);
            $this->tpl->setVariable('OBL_STATUS', $a_set['obligatory'] ? ' checked="checked"' : '');
            $this->tpl->parseCurrentBlock();
        }

        if ($this->getParentObject() !== null) {
            $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'condition_id', $a_set['id']);
            $this->tpl->setVariable(
                'EDIT_LINK',
                $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'edit')
            );
        }
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
    }

    /**
     * Set and parse conditions
     * @param array $a_conditions
     */
    public function setConditions(array $a_conditions): void
    {
        $rows = [];
        foreach ($a_conditions as $condition) {
            if ($condition['trigger_type'] === 'crsg') {
                continue;
            }
            $row['id'] = $condition['condition_id'];
            $row['ref_id'] = $condition['trigger_ref_id'];
            $row['type'] = $condition['trigger_type'];
            $row['title'] = ilObject::_lookupTitle($condition['trigger_obj_id']);
            $row['description'] = ilObject::_lookupDescription($condition['trigger_obj_id']);
            $row['icon'] = ilObject::_getIcon((int) $condition['trigger_obj_id']);
            $row['icon_alt'] = $this->lng->txt('obj_' . $condition['trigger_type']);
            $row['condition'] = $this->lng->txt('condition_' . $condition['operator']);
            $row['obligatory'] = $condition['obligatory'];

            $rows[] = $row;
        }
        $this->setData($rows);
    }

    /**
     * Init Table
     */
    protected function initTable(): void
    {
        $this->lng->loadLanguageModule('rbac');

        $this->setRowTemplate('tpl.condition_handler_row.html', 'Services/AccessControl');
        $this->setTitle($this->lng->txt('active_preconditions'));
        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('rbac_precondition_source'), 'title', '66%');
        $this->addColumn($this->lng->txt('condition'), 'condition');
        $this->addColumn($this->lng->txt('precondition_obligatory'), 'obligatory');
        $this->addColumn($this->lng->txt('actions'));

        $this->enable('select_all');
        $this->setSelectAllCheckbox('conditions');

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addMultiCommand('askDelete', $this->lng->txt('delete'));

        if ($this->enable_editing) {
            $this->addCommandButton("saveObligatoryList", $this->lng->txt("rbac_precondition_save_obligatory"));
        }
    }
}
