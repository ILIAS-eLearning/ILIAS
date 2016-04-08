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
}