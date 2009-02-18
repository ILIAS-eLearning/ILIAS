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

/**
* client management
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
*/

class ilClientList
{
	var $ini;			// ini file object
	var $path;			// clients base dir
	var $error = "";	// error text
	
	function ilClientList($a_db_connections)
	{		
		$this->db_connections = $a_db_connections;
		$this->path = ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR;
		$this->init();
	}

	/**
	* load all clients into clientlist
	*/
	function init()
	{
		// set path to directory where clients reside
		$d = dir($this->path);
//		$tmpPath = getcwd();
//		chdir ($this->path);

		// get available lang-files
		while ($entry = $d->read())
		{
			if (is_file($this->path."/".$entry."/client.ini.php"))
			{
				$client = new ilClient($entry, $this->db_connections);
				$client->init();
				
				$this->clients[$entry] = $client;
				
				unset($client);
			}
		}

//		chdir($tmpPath);
	}
	
	/**
	* get all clients
	* @return	array	array with client objects
	*/
	function getClients()
	{
		return ($this->clients) ? $this->clients : array();
	}
}
?>