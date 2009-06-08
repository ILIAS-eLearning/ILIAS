<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once ("./Services/Database/classes/class.ilDB.php");

/**
* DB Wrapper Factory. Delivers a DB wrapper object depending on given
* DB type and DSN.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBWrapperFactory
{
	static function getWrapper($a_type)
	{
		global $ilClientIniFile;
		
		if ($a_type == "" && is_object($ilClientIniFile))
		{
			$a_type = $ilClientIniFile->readVariable("db","type");
		}
		if ($a_type == "")
		{
			$a_type = "mysql";
		}
		
		switch ($a_type)
		{
			case "mysql":
				include_once("./Services/Database/classes/class.ilDBMySQL.php");
				$ilDB = new ilDBMySQL();
				break;

			case "oracle":
				include_once("./Services/Database/classes/class.ilDBOracle.php");
				$ilDB = new ilDBOracle();
				break;
		}
		
		return $ilDB;
	}
}
