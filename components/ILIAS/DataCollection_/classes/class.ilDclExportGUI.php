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

class ilDclExportGUI extends ilExportGUI
{
    protected function buildExportTableGUI(): ilExportTableGUI
    {
        return new ilDclExportTableGUI($this, 'listExportFiles', $this->obj);
    }

    /**
     * overwrite to check if exportable fields are available (for async xls export)
     */
    public function createExportFile(): void
    {
        $format = "";
        if ($this->http->wrapper()->post()->has('format')) {
            $format = $this->http->wrapper()->post()->retrieve('format', $this->refinery->kindlyTo()->string());
        }
        if ($format === 'xlsx') {
            $this->checkForExportableFields();
        }

        parent::createExportFile();
    }

    /**
     * send failure and redirect if no exportable fields
     */
    protected function checkForExportableFields(): bool
    {
        foreach ($this->obj->getTables() as $tbl) {
            /** @var $tbl ilDclTable */
            foreach ($tbl->getFields() as $field) {
                if ($field->getExportable()) {
                    return true;
                }
            }
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_no_export_data_available'), true);
        $this->ctrl->redirect($this, "listExportFiles");

        return false;
    }
}
