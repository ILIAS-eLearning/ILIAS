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
 ********************************************************************
 */

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
    public function fillExcelExport(ilExcel $worksheet, int &$row, int &$col) : void
    {
        $worksheet->setCell($row, $col, $this->getExportValue(), DataType::TYPE_STRING);
        $col++;
    }
}
