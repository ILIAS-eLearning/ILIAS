<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './libs/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/*
 * Wrapper for Microsoft Excel Import/Export (based on PHPSpreadsheet, formerPHPExcel which is deprecated)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesExcel
 */
class ilExcel
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var PhpOffice\PhpSpreadsheet\
     */
    protected $workbook; // [PhpSpreadsheet]
    
    /**
     * @var string
     */
    protected $type; // [string]
    
    const FORMAT_XML = "Xlsx";
    const FORMAT_BIFF = "Xls";
    
    /**
     * Constructor
     *
     * @return self
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
     * @param $filename
     */
    public function loadFromFile($filename)
    {
        $this->workbook = IOFactory::load($filename);
    }
    
    //
    // type/format
    //
    
    /**
     * Get valid file formats
     *
     * @return array
     */
    public function getValidFormats()
    {
        return array(self::FORMAT_XML, self::FORMAT_BIFF);
    }
    
    /**
     * Set file format
     *
     * @param string $a_format
     */
    public function setFormat($a_format)
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
     * @param string $a_name
     * @param bool $a_activate
     * @return int index
     */
    public function addSheet($a_name, $a_activate = true)
    {
        #20749
        // see Worksheet::$_invalidCharacters;
        $invalid = array('*', ':', '/', '\\', '?', '[', ']', '\'-','\'');
        
        $a_name = str_replace($invalid, "", $a_name);
        
        // #19056 - phpExcel only allows 31 chars
        // see https://github.com/PHPOffice/PHPExcel/issues/79
        $a_name = ilUtil::shortenText($a_name, 31);
        
        $sheet = new Worksheet($this->workbook, $a_name);
        $this->workbook->addSheet($sheet);
        $new_index = $this->workbook->getSheetCount() - 1;
        
        if ((bool) $a_activate) {
            $this->setActiveSheet($new_index);
        }
        
        return $new_index;
    }
    
    /**
     * Set active sheet
     *
     * @param int $a_index
     */
    public function setActiveSheet($a_index)
    {
        $this->workbook->setActiveSheetIndex($a_index);
    }


    /**
     * Returns number of sheets
     *
     * @return int
     */
    public function getSheetCount()
    {
        return $this->workbook->getSheetCount();
    }


    /**
     * Return the current sheet title
     *
     * @return string
     */
    public function getSheetTitle()
    {
        return $this->workbook->getActiveSheet()->getTitle();
    }
    
    
    //
    // cells
    //
    
    /**
     * Prepare value for cell
     *
     * @param mixed $a_value
     * @return mixed
     */
    protected function prepareValue($a_value)
    {
        $lng = $this->lng;
        
        // :TODO: does this make sense?
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
     * @return string
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

    /**
     * @param bool $a_value
     * @return string
     */
    protected function prepareBooleanValue($a_value)
    {
        $lng = $this->lng;

        return $a_value ? $lng->txt('yes') : $lng->txt('no');
    }

    /**
     * @param string $a_value
     * @return string
     */
    protected function prepareString($a_value)
    {
        return strip_tags($a_value); // #14542
    }

    /**
     * Set date format
     *
     * @param Cell $a_cell
     * @param mixed $a_value
     */
    protected function setDateFormat(Cell $a_cell, $a_value)
    {
        if ($a_value instanceof ilDate) {
            // :TODO: i18n?
            $a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy");
        } elseif ($a_value instanceof ilDateTime) {
            // :TODO: i18n?
            $a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy hh:mm:ss");
        }
    }
    
    /**
     * Set cell value by coordinates
     *
     * @param string $a_coords
     * @param mixed $a_value
     */
    public function setCellByCoordinates($a_coords, $a_value)
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
     * @param null  $a_datatype
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function setCell($a_row, $a_col, $a_value, $a_datatype = null)
    {
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
     * @param mixed $a_null_value
     */
    public function setCellArray(array $a_values, $a_top_left = "A1", $a_null_value = null)
    {
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
     *
     * @param int $a_row
     * @param int $a_col
     *
     * @return mixed
     */
    public function getCell($a_row, $a_col)
    {
        $col = $this->columnIndexAdjustment($a_col);

        return $this->workbook->getActiveSheet()->getCellByColumnAndRow($col, $a_row)->getValue();
    }


    /**
     * Returns the active sheet as an array
     *
     * @return array
     */
    public function getSheetAsArray()
    {
        return $this->workbook->getActiveSheet()->toArray();
    }


    /**
     * Returns the number of columns the sheet contains
     *
     * @return int
     */
    public function getColumnCount()
    {
        return Coordinate::columnIndexFromString($this->workbook->getActiveSheet()->getHighestDataColumn());
    }

    /**
     * Get column "name" from number
     *
     * @param int $a_col
     * @return string
     */
    public function getColumnCoord($a_col)
    {
        $col = $this->columnIndexAdjustment($a_col);

        return Coordinate::stringFromColumnIndex($col);
    }
    
    /**
     * Set all existing columns on all sheets to autosize
     */
    protected function setGlobalAutoSize()
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
    protected function prepareStorage($a_file_name)
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
     *
     * @param string $a_file_name
     */
    public function sendToClient($a_file_name)
    {
        require_once('./Services/FileDelivery/classes/class.ilPHPOutputDelivery.php');

        $a_file_name = $this->prepareStorage($a_file_name);
        switch ($this->format) {
            case self::FORMAT_BIFF:
                $a_mime_type = ilMimeTypeUtil::APPLICATION__VND_MS_EXCEL;
                break;

            case self::FORMAT_XML:
                $a_mime_type = ilMimeTypeUtil::APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET;
                break;
            default:
                $a_mime_type = ilMimeTypeUtil::APPLICATION__OCTET_STREAM;
                break;
        }
        $tmp_name = ilUtil::ilTempnam();

        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $writer->save($tmp_name);

        ilFileDelivery::deliverFileAttached($tmp_name, $a_file_name, $a_mime_type, true);
    }
    
    /**
     * Save workbook to file
     *
     * @param string $a_file full path
     */
    public function writeToFile($a_file)
    {
        $a_file = $this->prepareStorage($a_file);
        
        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $writer->save($a_file);
    }


    /**
     * @return string
     * @throws \PHPExcel_Reader_Exception
     */
    public function writeToTmpFile()
    {
        $writer = IOFactory::createWriter($this->workbook, $this->format);
        $filename = ilUtil::ilTempnam();
        $writer->save($filename);
        
        return $filename;
    }

    //
    // style (:TODO: more options?)
    //
    
    /**
     * Set cell(s) to bold
     *
     * @param string $a_coords
     */
    public function setBold($a_coords)
    {
        $this->workbook->getActiveSheet()->getStyle($a_coords)->getFont()->setBold(true);
    }
    
    /**
     * Set cell(s) colors
     *
     * @param string $a_coords
     * @param string $a_background
     * @param string $a_font
     */
    public function setColors($a_coords, $a_background, $a_font = null)
    {
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
     *
     * @param string $a_coords
     * @param bool $a_top
     * @param bool $a_right
     * @param bool $a_bottom
     * @param bool $a_left
     */
    public function setBorders($a_coords, $a_top, $a_right = false, $a_bottom = false, $a_left = false)
    {
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
     *
     * @param int $pColumn
     * @param int $pRow
     * @return string
     */
    public function getCoordByColumnAndRow($pColumn = 1, $pRow = 1)
    {
        $col = $this->columnIndexAdjustment($pColumn);
        $columnLetter = Coordinate::stringFromColumnIndex($col);

        return $columnLetter . $pRow;
    }

    /**
     * @param $a_row int
     * @param $a_column int
     * @param $a_path
     */
    public function addLink($a_row, $a_column, $a_path)
    {
        $column = $this->columnIndexAdjustment($a_column);

        $this->workbook->getActiveSheet()->getCellByColumnAndRow($column, $a_row)->getHyperlink()->setUrl($a_path);
    }

    /**
     * Adjustment needed because of migration PHPExcel to PhpSpreadsheet.
     * PhpExcel column was 0 index based and PhpSpreadshet set this index to 1
     * @param $column
     * @return int
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
