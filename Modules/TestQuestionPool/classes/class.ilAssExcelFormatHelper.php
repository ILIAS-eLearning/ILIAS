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
	 * @param ilExcel $worksheet
	 * @param string $coordinates
	 * @param string $value
	 */
	public static function setFormatedExcelTitle($worksheet, $coordinates, $value)
	{
		$worksheet->setCellByCoordinates($coordinates, $value);
		$worksheet->setColors($coordinates, EXCEL_BACKGROUND_COLOR);
		$worksheet->setBold($coordinates);
	}
}