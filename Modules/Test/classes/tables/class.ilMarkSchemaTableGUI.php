<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';

/**
 * Class ilMarkSchemaGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
class ilMarkSchemaTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilMarkSchemaAware
     */
    protected $object;

    /**
     * @var bool
     */
    protected $is_editable = true;

    /**
     * @param        $parent
     * @param string $cmd
     */
    public function __construct($parent, $cmd, $template_context = '', ilMarkSchemaAware $object = null)
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->object = $object;
        $this->ctrl   = $ilCtrl;
        
        $this->is_editable = $this->object->canEditMarks();

        $this->setId('mark_schema_gui_' . $this->object->getMarkSchemaForeignId());
        parent::__construct($parent, $cmd);

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $cmd));
        $this->setFormName('form_' . $this->getId());

        $this->setRowTemplate('tpl.il_as_tst_mark_schema_row.html', 'Modules/Test');

        $this->setNoEntriesText($this->lng->txt('tst_no_marks_defined'));

        if ($this->object->canEditMarks()) {
            $this->addCommandButton('saveMarks', $this->lng->txt('save'));
            $this->addMultiCommand('deleteMarkSteps', $this->lng->txt('delete'));

            $this->setSelectAllCheckbox('marks[]');
        } else {
            $this->disable('select_all');
        }

        $this->setLimit(PHP_INT_MAX);

        $this->initColumns();
        $this->initData();
    }

    /**
     *
     */
    protected function initColumns()
    {
        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('tst_mark_short_form'), '');
        $this->addColumn($this->lng->txt('tst_mark_official_form'), '');
        $this->addColumn($this->lng->txt('tst_mark_minimum_level'), '');
        $this->addColumn($this->lng->txt('tst_mark_passed'), '', '1');
    }

    /**
     *
     */
    protected function initData()
    {
        $this->object->getMarkSchema()->sort();

        $data = array();

        $marks = $this->object->getMarkSchema()->getMarkSteps();
        foreach ($marks as $key => $value) {
            $data[] = array(
                'mark_id'         => $key,
                'mark_short'      => $value->getShortName(),
                'mark_official'   => $value->getOfficialName(),
                'mark_percentage' => $value->getMinimumLevel(),
                'mark_passed'     => $value->getPassed() ? 1 : 0
            );
        }

        $this->setData($data);
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        $short_name = new ilTextInputGUI('', 'mark_short_' . $row['mark_id']);
        $short_name->setValue($row['mark_short']);
        $short_name->setDisabled(!$this->is_editable);
        $short_name->setSize(10);

        $official_name = new ilTextInputGUI('', 'mark_official_' . $row['mark_id']);
        $official_name->setSize(20);
        $official_name->setDisabled(!$this->object->canEditMarks());
        $official_name->setValue($row['mark_official']);

        $percentage = new ilNumberInputGUI('', 'mark_percentage_' . $row['mark_id']);
        $percentage->allowDecimals(true);
        $percentage->setValue($row['mark_percentage']);
        $percentage->setSize(10);
        $percentage->setDisabled(!$this->is_editable);
        $percentage->setMinValue(0);
        $percentage->setMaxValue(100);

        $this->tpl->setVariable('VAL_MARK_ID', $row['mark_id']);
        $this->tpl->setVariable('VAL_CHECKBOX', ilUtil::formCheckbox(false, 'marks[]', $row['mark_id'], !$this->is_editable));
        $this->tpl->setVariable('VAL_SHORT_NAME', $short_name->render());
        $this->tpl->setVariable('VAL_OFFICIAL_NAME', $official_name->render());
        $this->tpl->setVariable('VAL_PERCENTAGE', $percentage->render());
        $this->tpl->setVariable('VAL_PASSED_CHECKBOX', ilUtil::formCheckbox((bool) $row['mark_passed'], 'passed_' . $row['mark_id'], '1', !$this->is_editable));
    }
}
