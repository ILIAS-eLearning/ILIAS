<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Services/Excel/classes/class.ilExcel.php';

/**
 * Class ilAssExcelFormatHelper
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilAssExcelFormatHelper extends ilExcel
{
    const escapeString = true;

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
    public function setFormattedExcelTitle($coordinates, $value)
    {
        $this->setCellByCoordinates($coordinates, $value);
        $this->setColors($coordinates, EXCEL_BACKGROUND_COLOR);
        $this->setBold($coordinates);
    }

    /**
     * @inheritdoc
     */
    public function setCellByCoordinates($a_coords, $a_value)
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
    public function setCell($a_row, $a_col, $a_value)
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
    protected function prepareString($a_value)
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
    public function setStringEscaping($stringEscaping)
    {
        $this->stringEscaping = $stringEscaping;
    }
}
