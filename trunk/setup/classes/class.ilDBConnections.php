<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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



/**
* Administrates DB connections in setup.
*
* Manage DB Connections
*/
class ilDBConnections
{
	var $db;
	var $log;

	function ilDBConnections()
	{
		$this->connections = array();
	}

	function connectHost($a_dsn_host)
	{
//echo "<br>connectingHost:".$a_dsn_host;
		$db = MDB2::connect($a_dsn_host);
		return $db;
	}
	
	function connectDB($a_dsn_db)
	{
//echo "<br>connectingDB:".$a_dsn_db;
		return MDB2::connect($a_dsn_db);
	}
}
?>
