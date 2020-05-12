<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilTestPersonalDefaultSettingsTableGUI
 */
class ilTestPersonalDefaultSettingsTableGUI extends ilTable2GUI
{
    public function __construct($parentObj, $cmd)
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setId('tst_pers_def_set_' . $parentObj->object->getId());

        parent::__construct($parentObj, $cmd);

        $this->setTitle($this->lng->txt('tst_defaults_available'));
        $this->setNoEntriesText($this->lng->txt('tst_defaults_not_defined'));
        $this->setFormAction($ilCtrl->getFormAction($parentObj, $cmd));

        $this->setRowTemplate('tpl.il_as_tst_defaults_row.html', 'Modules/Test');

        $this->setShowRowsSelector(true);
        $this->setSelectAllCheckbox('chb_defaults');
        $this->setFormName('formDefaults');
        $this->addMultiCommand('deleteDefaults', $this->lng->txt('delete'));
        $this->addMultiCommand('applyDefaults', $this->lng->txt('apply_def_settings_to_tst'));
        $this->initColumns();
    }

    /**
     *
     */
    private function initColumns()
    {
        $this->addColumn('', '', '1px', true);
        $this->addColumn($this->lng->txt('title'), 'name', '80%');
        $this->addColumn($this->lng->txt('date'), 'tstamp', '19%');
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        parent::fillRow(array(
            'name' => $row['name'],
            'checkbox' => ilUtil::formCheckbox(false, 'chb_defaults[]', $row['test_defaults_id']),
            'tstamp' => ilDatePresentation::formatDate(new ilDateTime($row['tstamp'], IL_CAL_UNIX))
        ));
    }

    /**
     * @return bool
     */
    public function numericOrdering($field)
    {
        return in_array($field, array(
            'tstamp'
        ));
    }
}
