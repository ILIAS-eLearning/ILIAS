<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Class ilDclTextSelectionRecordFieldModel
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTextSelectionRecordFieldModel extends ilDclSelectionRecordFieldModel
{
    const PROP_SELECTION_TYPE = 'text_selection_type';
    const PROP_SELECTION_OPTIONS = 'text_selection_options';

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function fillExcelExport(ilExcel $worksheet, int &$row, int &$col): void
    {
        $worksheet->setCell($row, $col, $this->getExportValue(), DataType::TYPE_STRING);
        $col++;
    }
}
