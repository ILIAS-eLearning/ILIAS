<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/PEAR/lib/Spreadsheet/Excel/Writer.php';

/**
* Class ilExcelWriterWrapper
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilExcelWriterAdapter.php 23143 2010-03-09 12:15:33Z smeyer $
*
* @extends Spreadsheet_Excel_Writer
*
*/
class ilExcelWriterWrapper extends Spreadsheet_Excel_Writer
{
   function addWorksheet($name = '')
   {
      $worksheet = parent::addWorksheet($name);

	  // we need this to make utf8 work properly (in combination with version 8)
	  $worksheet->setInputEncoding("UTF-8");

	  return $worksheet;
   }
}

?>