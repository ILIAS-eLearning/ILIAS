<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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
