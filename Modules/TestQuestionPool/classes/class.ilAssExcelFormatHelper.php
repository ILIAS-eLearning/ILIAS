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

require_once 'Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class ilAssExcelFormatHelper
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilAssExcelFormatHelper extends ilExcel
{
    public const escapeString = true;

    protected $stringEscaping = self::escapeString;

    /**
     * ilAssExcelFormatHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $coordinates
     * @param string $value
     */
    public function setFormattedExcelTitle($coordinates, $value): void
    {
        $this->setCellByCoordinates($coordinates, $value);
        $this->setColors($coordinates, EXCEL_BACKGROUND_COLOR);
        $this->setBold($coordinates);
    }

    /**
     * @inheritdoc
     */
    public function setCellByCoordinates($a_coords, $a_value): void
    {
        if (is_string($a_value) && !is_numeric($a_value)) {
            $this->workbook->getActiveSheet()->setCellValueExplicit(
                $a_coords,
                $this->prepareValue($a_value),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
                true
            );
        } else {
            parent::setCellByCoordinates($a_coords, $a_value);
        }
    }

    /**
     * @inheritdoc
     */
    public function setCell($a_row, $a_col, $a_value, $datatype = null): void
    {
        if (is_string($a_value) && !is_numeric($a_value)) {
            $this->workbook->getActiveSheet()->setCellValueExplicitByColumnAndRow(
                $a_col + 1,
                $a_row,
                $this->prepareValue($a_value),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
                true
            );
        } else {
            parent::setCell($a_row, $a_col, $a_value);
        }
    }

    /**
     * @param string $a_value
     * @return string
     */
    protected function prepareString($a_value): string
    {
        if ($this->stringEscaping == false) {
            return $a_value;
        } else {
            return strip_tags($a_value);
        }
    }

    /**
     * @return int
     */
    public function getStringEscaping()
    {
        return $this->stringEscaping;
    }

    /**
     * @param int $stringEscaping
     */
    public function setStringEscaping($stringEscaping): void
    {
        $this->stringEscaping = $stringEscaping;
    }
}
