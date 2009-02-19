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
* Oracle Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain Oracle specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBOracle extends ilDB
{

	/**
	* Get DSN.
	*/
	function getDSN()
	{
		return "oci8://".$this->getDBUser().":".$this->getDBPassword()."@".
			$this->getDBHost()."/?service=".$this->getDBName();
	}

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "oracle";
	}
	
	/**
	* Get reserved words
	*/
	static function getReservedWords()
	{
		// version: 10g
		// url: http://download-west.oracle.com/docs/cd/B14117_01/server.101/b10759/ap_keywd.htm#g691972
		return array(
			"ACCESS", "ADD", "ALL", "ALTER", "AND", "ANY", "AS", "ASC",
			"AUDIT", "BETWEEN", "BY", "CHAR", "CHECK", "CLUSTER", "COLUMN",
			"COMMENT", "COMPRESS", "CONNECT", "CREATE", "CURRENT", "DATE",
			"DECIMAL", "DEFAULT", "DELETE", "DESC", "DISTINCT", "DROP", "ELSE",
			"EXCLUSIVE", "EXISTS", "FILE", "FLOAT", "FOR", "FROM", "GRANT", "GROUP",
			"HAVING", "IDENTIFIED", "IMMEDIATE", "IN", "INCREMENT", "INDEX", "INITIAL",
			"INSERT", "INTEGER", "INTERSECT", "INTO", "IS", "LEVEL", "LIKE", "LOCK", "LONG",
			"MAXEXTENTS", "MINUS", "MLSLABEL", "MODE", "MODIFY", "NOAUDIT", "NOCOMPRESS", "NOT",
			"NOWAIT", "NULL", "NUMBER", "OF", "OFFLINE", "ON", "ONLINE","OPTION",
			"OR", "ORDER", "PCTFREE", "PRIOR", "PRIVILEGES", "PUBLIC", "RAW", "RENAME",
			"RESOURCE", "REVOKE", "ROW", "ROWID", "ROWNUM", "ROWS", "SELECT", "SESSION", "SET",
			"SHARE", "SIZE", "SMALLINT", "START", "SUCCESSFUL", "SYNONYM", "SYSDATE","TABLE",
			"THEN", "TO", "TRIGGER", "UID", "UNION", "UNIQUE", "UPDATE", "USER","VALIDATE",
			"VALUES", "VARCHAR", "VARCHAR2", "VIEW", "WHENEVER", "WHERE", "WITH"
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
	
	/**
	* Constraint names must be "globally" unique in oracle.
	*/
	function constraintName($a_table, $a_constraint)
	{
		return $a_table."_".$a_constraint;
	}

}
?>
