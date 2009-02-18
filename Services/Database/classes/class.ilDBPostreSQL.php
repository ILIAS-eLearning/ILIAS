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
* PostreSQL Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain PostreSQL specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBPostgreSQL extends ilDB
{

	/**
	* Get DSN.
	*/
	function getDSN()
	{
		return "pgsql://".$this->getDBUser().":".$this->getDBPassword()."@".
			$this->getDBHost()."/".$this->getDBName();
	}

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "postgresql";
	}
	
	/**
	* Get reserved words
	*/
	function getReservedWords()
	{
		// version: 8.3.6
		// url: http://www.postgresql.org/docs/current/static/sql-keywords-appendix.html
		return array(
			"ALL", "ANALYSE", "ANALYZE", "AND", "ANY", "ARRAY",
			"AS", "ASC", "ASYMMETRIC", "AUTHORIZATION", "BETWEEN", "BINARY", "BOTH",
			"CASE", "CAST", "CHECK", "COLLATE", "COLUMN", "CONSTRAINT", "CREATE",
			"CROSS", "CURRENT_DATE", "CURRENT_ROLE", "CURRENT_TIME", "CURRENT_TIMESTAMP", "CURRENT_USER", "DEFAULT",
			"DEFERRABLE", "DESC", "DISTINCT", "DO", "ELSE", "END", "EXCEPT",
			"FALSE", "FOR", "FOREIGN", "FREEZE", "FROM", "FULL", "GRANT",
			"GROUP", "HAVING", "ILIKE", "IN", "INITIALLY", "INNER", "INTERSECT",
			"INTO", "IS", "ISNULL", "JOIN", "LEADING", "LEFT", "LIKE",
			"LIMIT", "LOCALTIME", "LOCALTIMESTAMP", "NATURAL", "NEW", "NOT", "NOTNULL",
			"NULL", "OFF", "OFFSET", "OLD", "ON", "ONLY", "OR",
			"ORDER", "OUTER", "OVERLAPS", "PLACING", "PRIMARY", "REFERENCES", "RETURNING",
			"RIGHT", "SELECT", "SESSION_USER", "SIMILAR", "SOME", "SYMMETRIC", "TABLE",
			"THEN", "TO", "TRAILING", "TRUE", "UNION", "UNIQUE", "USER",
			"USING", "VERBOSE", "WHEN", "WHERE", "WITH"
		);
	}

	/**
	* Initialize the database connection
	*/
	function initConnection()
	{
	}

	/**
	* now()
	* @todo fix this
	*/
	function now()
	{
		return "now()";
	}
}
?>
