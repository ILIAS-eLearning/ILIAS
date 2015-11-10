<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* trait for gev WBD-Success
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/Calendar/classes/class.ilDate.php");
trait gevWBDSuccess{

	protected function createDate($iso_date_string) {
		$split = explode(self::DATE_SPLITTER,$iso_date_string);
		return new ilDate($split[0],IL_CALC_DATE);
	}
}