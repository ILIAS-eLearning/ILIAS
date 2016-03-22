<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './libs/composer/vendor/autoload.php';

/*
 * Wrapper for Microsoft Excel Import/Export (based on PHPExcel)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesExcel
 */
class ilExcel
{
	protected $workbook; // [PHPExcel]
	
	const TYPE_EXCEL_XML = "Excel2007";
	const TYPE_EXCEL_BIFF = "Excel5";
	
	public function __construct()
	{
		$this->setType(self::TYPE_EXCEL_XML);		
		$this->workbook = new PHPExcel();	
		$this->workbook->removeSheetByIndex(0);
	}		
	
	
	// 
	// type/format
	// 
	
	public function getValidTypes()
	{
		return array(self::TYPE_EXCEL_BIFF, self::TYPE_EXCEL_XML);
	}
	
	public function setType($a_type)
	{
		if(in_array($a_type, $this->getValidTypes()))
		{
			$this->type = $a_type;
		}
	}
	
	
	//
	// sheets
	//
	
	public function addSheet($a_name, $a_activate = true)
	{
		$sheet = new PHPExcel_Worksheet($this->workbook, $a_name);
		$this->workbook->addSheet($sheet);
		$new_index = $this->workbook->getSheetCount()-1;
		
		if((bool)$a_activate)
		{
			$this->setActiveSheet($new_index);
		}
		
		return $new_index;		
	}
	
	public function setActiveSheet($a_index)
	{
		$this->workbook->setActiveSheetIndex($a_index);
	}
	
	
	//
	// cells
	//
	
	protected function prepareValue($a_value)
	{
		global $lng;
		
		// :TODO: does this make sense?
		if(is_bool($a_value))
		{
			$a_value = $a_value
				? $lng->txt("yes")
				: $lng->txt("no");
		}
		else if($a_value instanceof ilDate)
		{
			$a_value = PHPExcel_Shared_Date::stringToExcel($a_value->get(IL_CAL_DATE));			
		}	
		else if($a_value instanceof ilDateTime)
		{
			$a_value = PHPExcel_Shared_Date::stringToExcel($a_value->get(IL_CAL_DATETIME));
		}
		
		return $a_value;
	}
	
	protected function setDateFormat(PHPExcel_Cell $a_cell, $a_value)
	{
		if($a_value instanceof ilDate)
		{
			// :TODO: i18n?
			$a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy");
		}
		else if($a_value instanceof ilDateTime)
		{
			// :TODO: i18n?
			$a_cell->getStyle()->getNumberFormat()->setFormatCode("dd.mm.yyyy hh:mm:ss");
		}
	}
	
	public function setCellByCoordinates($a_coords, $a_value)
	{
		$cell = $this->workbook->getActiveSheet()->setCellValue(
			$a_coords, 
			$this->prepareValue($a_value),
			true
		);		
		$this->setDateFormat($cell, $a_value);		
	}
	
	public function setCell($a_row, $a_col, $a_value)
	{
		$cell = $this->workbook->getActiveSheet()->setCellValueByColumnAndRow(
			$a_col, 
			$a_row,			 
			$this->prepareValue($a_value),
			true
		);			
		$this->setDateFormat($cell, $a_value);		
	}
	
	public function setCellArray(array $a_values, $a_top_left = "A1", $a_null_value = NULL)	
	{
		foreach($a_values as $row_idx => $cols)
		{
			if(is_array($cols))
			{
				foreach($cols as $col_idx => $col_value)
				{
					$a_values[$row_idx][$col_idx] = $this->prepareValue($col_value);
				}
			}
			else
			{
				$a_values[$row_idx] = $this->prepareValue($cols);
			}
		}
		
		$this->workbook->getActiveSheet()->fromArray($a_values, $a_null_value, $a_top_left);
	}

	
	//
	// deliver/save
	// 
	
	public function sendToClient($a_file_name)
	{
		switch($this->type)
		{
			case self::TYPE_EXCEL_BIFF:
				header("Content-Type: application/vnd.ms-excel; charset=utf-8");
				if(!stristr($a_file_name, ".xls"))
				{
					$a_file_name .= ".xls";
				}
				break;
			
			case self::TYPE_EXCEL_XML:
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
				if(!stristr($a_file_name, ".xls"))
				{
					$a_file_name .= ".xlsx";
				}
				break;
		}
		
		header('Content-Disposition: attachment;filename="'.$a_file_name.'"');
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		
		$writer = PHPExcel_IOFactory::createWriter($this->workbook, $this->type);
		$writer->save('php://output');
	}
	
	public function writeToFile($a_file)
	{
		$writer = PHPExcel_IOFactory::createWriter($this->workbook, $this->type);
		$writer->write($a_file);
	}		
	
	
	// 
	// style (:TODO: more wrapping?)
	// 
	
	public function setBold($a_coords)
	{
		$this->workbook->getActiveSheet()->getStyle($a_coords)->getFont()->setBold(true);
	}
	
	public function setColors($a_coords, $a_background, $a_font = null)
	{
		$opts = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => $a_background)
			)
		);   
		
		if($a_font)
		{
			$opts['font'] = array(
				'color' => array('rgb' => $a_font)
			);
		}
		
		$this->workbook->getActiveSheet()->getStyle($a_coords)->applyFromArray($opts);
	}
	
	public function setBorders($a_coords, $a_top, $a_right = false, $a_bottom = false, $a_left = false)
	{
		$style = $this->workbook->getActiveSheet()->getStyle($a_coords);
		
		// :TODO: border styles?
		if($a_top)
		{
			$style->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
		if($a_right)
		{
			$style->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
		if($a_bottom)
		{
			$style->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
		if($a_left)
		{
			$style->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
	}			
	
	
	//
	// testing
	//
	
	public function test()
	{				
		$index = $this->addSheet("test");
		$index = $this->addSheet("test2");
		
		$this->setCell(1, 0, 33);
		$this->setCell(1, 1, 34);
		$this->setCell(1, 2, 35);
		$this->setCellByCoordinates("B2", 5.4);
		$this->setCell(3, 3, true);
		$this->setCell(4, 3, new ilDate(time(), IL_CAL_UNIX));
		$this->setCell(5, 3, new ilDateTime(time(), IL_CAL_UNIX));
		
		$this->setCellArray(array(
			array(1.1, 1.2, 1.3, null),
			array(2.1, 2.2, null, 2.4),
			array(true, false, "555", 55),
		), "D8");
		
		$this->setBold("A1:F1");	
		$this->setColors("A1:F1", "FF0000", "FFFF00");		
		$this->setBorders("A1:F1", false, false, true);		
			
		$this->sendToClient("test");		
	}
}