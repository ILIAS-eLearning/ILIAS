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

        $this->setId('tst_pers_def_set_' . $parentObj->getObject()->getId());

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

    private function initColumns(): void
    {
        $this->addColumn('', '', '1px', true);
        $this->addColumn($this->lng->txt('title'), 'name', '80%');
        $this->addColumn($this->lng->txt('date'), 'tstamp', '19%');
    }

    public function fillRow(array $a_set): void
    {
        parent::fillRow(array(
            'name' => $a_set['name'],
            'checkbox' => ilLegacyFormElementsUtil::formCheckbox(false, 'chb_defaults[]', $a_set['test_defaults_id']),
            'tstamp' => ilDatePresentation::formatDate(new ilDateTime($a_set['tstamp'], IL_CAL_UNIX))
        ));
    }

    public function numericOrdering(string $a_field): bool
    {
        return in_array($a_field, array(
            'tstamp'
        ));
    }
}
