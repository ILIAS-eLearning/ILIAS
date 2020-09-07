<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export User Interface Class
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 */
class ilDclExportGUI extends ilExportGUI
{

    /**
     * @return ilTestExportTableGUI
     */
    protected function buildExportTableGUI()
    {
        $table = new ilDclExportTableGUI($this, 'listExportFiles', $this->obj);

        return $table;
    }


    /**
     * overwrite to check if exportable fields are available (for async xls export)
     */
    public function createExportFile()
    {
        if ($_POST['format'] == 'xlsx') {
            $this->checkForExportableFields();
        }

        return parent::createExportFile();
    }


    /**
     * send failure and redirect if no exportable fields
     *
     * @return bool
     */
    protected function checkForExportableFields()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        foreach ($this->obj->getTables() as $tbl) {
            /** @var $tbl ilDclTable */
            foreach ($tbl->getFields() as $field) {
                if ($field->getExportable()) {
                    return true;
                }
            }
        }

        ilUtil::sendFailure($lng->txt('dcl_no_export_data_available'), true);
        $ilCtrl->redirect($this, "listExportFiles");
    }
}
