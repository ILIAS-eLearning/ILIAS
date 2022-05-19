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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use ILIAS\FileUpload\MimeType;

/*
 * Wrapper for Microsoft Excel Import/Export (based on PHPSpreadsheet, formerPHPExcel which is deprecated)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilExcel
{
    public const FORMAT_XML = "Xlsx";
    public const FORMAT_BIFF = "Xls";
    protected string $format;

    protected ilLanguage $lng;
    protected Spreadsheet $workbook;
    protected string $type;

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setFormat(self::FORMAT_XML);
        $this->workbook = new Spreadsheet();
        $this->workbook->removeSheetByIndex(0);
    }

    //
    // loading files
    //

    /**
     * Loads a spreadsheet from file
     */
    public function loadFromFile(string $filename) : void
    {
        $this->workbook = IOFactory::load($filename);
    }
    
    //
    // type/format
    //
    
    /**
     * Get valid file formats
     */
    public function getValidFormats() : array
    {
        return array(self::FORMAT_XML, self::FORMAT_BIFF);
    }
    
    /**
     * Set file format
     */
    public function setFormat(string $a_format) : void
    {
        if (in_array($a_format, $this->getValidFormats())) {
            $this->format = $a_format;
        }
    }
    
    
    //
    // sheets
    //
    
    /**
     * Add sheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSheet(
        string $a_name,
        bool $a_activate = true
    ) : int {
        #20749
        // see Worksheet::$_invalidCharacters;
        $invalid = array('*', ':', '/', '\\', '?', '[', ']', '\'-','\'');
        
        $a_name = str_replace($invalid, "", $a_name);
        
        // #19056 - phpExcel only allows 31 chars
        // see https://github.com/PHPOffice/PHPExcel/issues/79
        $a_name = ilStr::shortenTextExtended($a_name, 31);
        
        $sheet = new Worksheet($this->workbook, $a_name);
        $this->workbook->addSheet($sheet);
        $new_index = $this->workbook->getSheetCount() - 1;
        
        if ($a_activate) {
            $this->setActiveSheet($new_index);
        }
        
        return $new_index;
    }
    
    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function setActiveSheet(int $a_index) : void
    {
        $this->workbook->setActiveSheetIndex($a_index);
    }


    /**
     * Returns number of sheets
     */
    public function getSheetCount() : int
    {
        return $this->workbook->getSheetCount();
    }


    /**
     * Return the current sheet title
     */
    public function getSheetTitle() : string
    {
        return $this->workbook->getActiveSheet()->getTitle();
    }
    
    
    //
    // cells
    //
    
    /**
     * Prepare value for cell
     * @param mixed $a_value
     * @return mixed
     */
    protected function prepareValue($a_value)
    {
        if (is_bool($a_value)) {
            $a_value = $this->prepareBooleanValue($a_value);
        } elseif ($a_value instanceof ilDateTime) {
            $a_value = $this->prepareDateValue($a_value);
        } elseif (is_string($a_value)) {
            $a_value = $this->prepareString($a_value);
        }
        
        return $a_value;
    }

    /**
     * @param ilDateTime $a_value
     * @return false|float
     */
    protected function prepareDateValue(ilDateTime $a_value)
    {
        switch (true) {
            case $a_value instanceof ilDate:
                $a_value = Date::stringToExcel($a_value->get(IL_CAL_DATE));
                break;

            default:
                $a_value = Date::stringToExcel($a_value->get(IL_CAL_DATETIME));
                break;
        }

        return $a_value;
    }

    protected function prepareBooleanValue(bool $a_value) : string
    {
        $lng = $this->lng;

        return $a_value ? $lng->txt('yes') : $lng->txt('no');
    }

    protected function prepareString(string $a_value) : string
    {
        return strip_tags($a_value); // #14542
    }

    /**
     * Set date format of cell
     *
     * @param Cell $a_cell
     * @param mixed $a_value
     */
    protected function setDateFormat(Cell $a_cell, $a_value) : void
    {
        if ($a_value instanceof ilDate) {
            $a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy");
        } elseif ($a_value instanceof ilDateTime) {
            $a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy hh:mm:ss");
        }
    }
    
    /**
     * Set cell value by coordinates
     * @param string $a_coords Coordinate of the cell, eg: 'A1'
     * @param mixed $a_value
     */
    public function setCellByCoordinates($a_coords, $a_value) : void
    {
        if ($a_value instanceof ilDateTime) {
            $wb = $this->workbook->getActiveSheet()->setCellValue(
                $a_coords,
                $this->prepareValue($a_value)
            );
            $cell = $wb->getCell($a_coords);
            $this->setDateFormat($cell, $a_value);
        } elseif (is_numeric($a_value)) {
            $this->workbook->getActiveSheet()->setCellValueExplicit(
                $a_coords,
                $this->prepareValue($a_value),
                DataType::TYPE_NUMERIC
            );
        } else {
            $this->workbook->getActiveSheet()->setCellValueExplicit(
                $a_coords,
                $this->prepareValue($a_value),
                DataType::TYPE_STRING
            );
        }
    }

    /**
     * Set cell value
     * @param int   $a_row
     * @param int   $a_col
     * @param mixed $a_value
     * @param ?string  $a_datatype Explicit data type, see DataType::TYPE_*
     */
    public function setCell(
        int $a_row,
        int $a_col,
        $a_value,
        ?string $a_datatype = null
    ) : void {
        $col = $this->columnIndexAdjustment($a_col);

        if (!is_null($a_datatype)) {
            $this->workbook->getActiveSheet()->setCellValueExplicitByColumnAndRow(
                $col,
                $a_row,
                $this->prepareValue($a_value),
                $a_datatype
            );
        } elseif ($a_value instanceof ilDateTime) {
            $wb = $this->workbook->getActiveSheet()->setCellValueByColumnAndRow(
                $col,
                $a_row,
                $this->prepareValue($a_value)
            );
            $this->setDateFormat($wb->getCellByColumnAndRow($col, $a_row), $a_value);
        } elseif (is_numeric($a_value)) {
            $wb = $this->workbook->getActiveSheet()->setCellValueExplicitByColumnAndRow(
                $col,
                $a_row,
                $this->prepareValue($a_value),
                DataType::TYPE_NUMERIC
            );
        } else {
            $wb = $this->workbook->getActiveSheet()->setCellValueExplicitByColumnAndRow(
                $col,
                $a_row,
                $this->prepareValue($a_value),
                DataType::TYPE_STRING
            );
        }
    }
    
    /**
     * Set cell values from array
     *
     * @param array $a_values
     * @param string $a_top_left
     * @param mixed $a_null_value Value in source array that stands for blank cell
     */
    public function setCellArray(
        array $a_values,
        string $a_top_left = "A1",
        $a_null_value = null
    ) : void {
        foreach ($a_values as $row_idx => $cols) {
            if (is_array($cols)) {
                foreach ($cols as $col_idx => $col_value) {
                    $a_values[$row_idx][$col_idx] = $this->prepareValue($col_value);
                }
            } else {
                $a_values[$row_idx] = $this->prepareValue($cols);
            }
        }
        
        $this->workbook->getActiveSheet()->fromArray($a_values, $a_null_value, $a_top_left);
    }


    /**
     * Returns the value of a cell
     * @return mixed
     */
    public function getCell(
        int $a_row,
        int $a_col
    ) {
        $col = $this->columnIndexAdjustment($a_col);
        return $this->workbook->getActiveSheet()->getCellByColumnAndRow($col, $a_row)->getValue();
    }

    /**
     * Returns the active sheet as an array
     */
    public function getSheetAsArray() : array
    {
        return $this->workbook->getActiveSheet()->toArray();
    }

    /**
     * Returns the number of columns the sheet contains
     */
    public function getColumnCount() : int
    {
        return Coordinate::columnIndexFromString($this->workbook->getActiveSheet()->getHighestDataColumn());
    }

    /**
     * Get column "name" from number
     */
    public function getColumnCoord(int $a_col) : string
    {
        $col = $this->columnIndexAdjustment($a_col);
        return Coordinate::stringFromColumnIndex($col);
    }
    
    /**
     * Set all existing columns on all sheets to autosize
     */
    protected function setGlobalAutoSize() : void
    {
        // this may change the active sheet
        foreach ($this->workbook->getWorksheetIterator() as $worksheet) {
            $this->workbook->setActiveSheetIndex($this->workbook->getIndex($worksheet));
            $sheet = $this->workbook->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
    }
    
    //
    // deliver/save
    //
    
    /**
     * Prepare workbook for storage/delivery
     */
    protected function prepareStorage(string $a_file_name) : string
    {
        $this->setGlobalAutoSize();
        $this->workbook->setActiveSheetIndex(0);
        
        switch ($this->format) {
            case self::FORMAT_BIFF:
                if (!stristr($a_file_name, ".xls")) {
                    $a_file_name .= ".xls";
                }
                break;
            
            case self::FORMAT_XML:
                if (!stristr($a_file_name, ".xlsx")) {
                    $a_file_name .= ".xlsx";
                }
                break;
        }
        
        return $a_file_name;
    }
    
    /**
     * Send workbook to client
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function sendToClient(string $a_file_name) : void
    {
        $a_file_name = $this->prepareStorage($a_file_name);
        switch ($this->format) {
            case self::FORMAT_BIFF:
                $a_mime_type = MimeType::APPLICATION__VND_MS_EXCEL;
                break;

            case self::FORMAT_XML:
                $a_mime_type = MimeType::APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET;
                break;
            default:
                $a_mime_type = MimeType::APPLICATION__OCTET_STREAM;
                break;
        }
        $tmp_name = ilFileUtils::ilTempnam();

        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $writer->save($tmp_name);

        ilFileDelivery::deliverFileAttached($tmp_name, $a_file_name, $a_mime_type, true);
    }
    
    /**
     * Save workbook to file
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function writeToFile(string $a_file) : void
    {
        $a_file = $this->prepareStorage($a_file);
        
        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $writer->save($a_file);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function writeToTmpFile() : string
    {
        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $filename = ilFileUtils::ilTempnam();
        $writer->save($filename);
        
        return $filename;
    }
    
    /**
     * Set cell(s) to bold
     */
    public function setBold(string $a_coords) : void
    {
        $this->workbook->getActiveSheet()->getStyle($a_coords)->getFont()->setBold(true);
    }
    
    /**
     * Set cell(s) colors
     */
    public function setColors(
        string $a_coords,
        string $a_background,
        string $a_font = null
    ) : void {
        $opts = array(
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'color' => array('rgb' => $a_background)
            )
        );
        
        if ($a_font) {
            $opts['font'] = array(
                'color' => array('rgb' => $a_font)
            );
        }
        
        $this->workbook->getActiveSheet()->getStyle($a_coords)->applyFromArray($opts);
    }
    
    /**
     * Toggle cell(s) borders
     */
    public function setBorders(
        string $a_coords,
        bool $a_top,
        bool $a_right = false,
        bool $a_bottom = false,
        bool $a_left = false
    ) : void {
        $style = $this->workbook->getActiveSheet()->getStyle($a_coords);
        
        // :TODO: border styles?
        if ($a_top) {
            $style->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        }
        if ($a_right) {
            $style->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        }
        if ($a_bottom) {
            $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }
        if ($a_left) {
            $style->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    /**
     * Get cell coordinate (e.g. "B2") for column and row number
     */
    public function getCoordByColumnAndRow(
        int $pColumn = 1,
        int $pRow = 1
    ) : string {
        $col = $this->columnIndexAdjustment($pColumn);
        $columnLetter = Coordinate::stringFromColumnIndex($col);

        return $columnLetter . $pRow;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addLink(
        int $a_row,
        int $a_column,
        string $a_path
    ) : void {
        $column = $this->columnIndexAdjustment($a_column);
        $this->workbook->getActiveSheet()->getCellByColumnAndRow($column, $a_row)->getHyperlink()->setUrl($a_path);
    }

    /**
     * Adjustment needed because of migration PHPExcel to PhpSpreadsheet.
     * PhpExcel column was 0 index based and PhpSpreadshet set this index to 1
     */
    public function columnIndexAdjustment(int $column) : int
    {
        return ++$column;
    }

    /**
     * @param string $coordinatesRange A coordinates range string like 'A1:B5'
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function mergeCells(string $coordinatesRange) : void
    {
        $this->workbook->getActiveSheet()->mergeCells($coordinatesRange);
    }
}
