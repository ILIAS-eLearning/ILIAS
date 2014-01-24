<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("./Services/Database/classes/class.ilDBMySQL.php");

/**
* MySQL InnoDB Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain InnoDB specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesDatabase
*/
class ilDBInnoDB extends ilDBMySQL
{

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "innodb";
	}
	
	/**
	 * Initialize the database connection
	 */
	function initConnection()
	{
		// SET 'max_allowed_packet' (only possible for mysql version 4)
		$this->setMaxAllowedPacket();
		
		// NOTE: Two sourcecodes use this or a similar handling:
		// - classes/class.ilDB.php
		// - setup/classes/class.ilClient.php

		$this->query("SET NAMES utf8");
		if (DEVMODE == 1)
		{
			$this->query("SET SESSION SQL_MODE = 'ONLY_FULL_GROUP_BY'");
		}

		$this->query("SET SESSION STORAGE_ENGINE = 'INNODB'");
	}

	/**
	* Is fulltext index supported?
	*/
	function supportsFulltext()
	{
		return false;
	}

	protected function getCreateTableOptions()
	{
		// InnoDB is default engine for MySQL >= 5.5
		return array('type' => 'InnoDB');
	}

}
?>
