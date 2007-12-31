<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Only temporary Wiki table creator
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiDBCreator
{

	static function createTables()
	{
		global $ilDB, $ilSetting;
		
		$wiki_db = $ilSetting->get("wiki_db");

		if ($wiki_db <= 0)
		{
			// wiki data
			$q = "DROP TABLE IF EXISTS il_wiki_data";
			$ilDB->query($q);
			
			$q = "CREATE TABLE il_wiki_data (
				id int NOT NULL PRIMARY KEY,
				startpage varchar(200) NOT NULL DEFAULT '',
				short varchar(20) NOT NULL DEFAULT '',
				online TINYINT DEFAULT 0
				)";
			$ilDB->query($q);
	
			$q = "DROP TABLE IF EXISTS il_wiki_page";
			$ilDB->query($q);
			
			$q = "CREATE TABLE il_wiki_page (
				id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
				title varchar(200) NOT NULL DEFAULT '',
				wiki_id int NOT NULL
				)";
			$ilDB->query($q);
			
			$ilSetting->set("wiki_db", 1);
		}
		
		// 
		if ($wiki_db == 1)
		{
			$q = "DROP TABLE IF EXISTS page_history";
			$ilDB->query($q);
			$q = "CREATE TABLE page_history (
				page_id int NOT NULL DEFAULT 0,
				parent_type varchar(4) NOT NULL DEFAULT '',
				hdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				parent_id int,
				content mediumtext,
				PRIMARY KEY (page_id, parent_type, hdate)
				)";
			$ilDB->query($q);
			
			$ilSetting->set("wiki_db", 2);
		}

		if ($wiki_db == 2)
		{
			$q = "ALTER TABLE page_object ADD COLUMN user int DEFAULT 0";
			$ilDB->query($q);
			$q = "ALTER TABLE page_history ADD COLUMN user int DEFAULT 0";
			$ilDB->query($q);

			$ilSetting->set("wiki_db", 3);
		}

		if ($wiki_db == 3)
		{
			$q = "DROP TABLE IF EXISTS page_history";
			$ilDB->query($q);
			$q = "CREATE TABLE page_history (
				page_id int NOT NULL DEFAULT 0,
				parent_type varchar(4) NOT NULL DEFAULT '',
				hdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				parent_id int,
				nr int,
				user int,
				content mediumtext,
				PRIMARY KEY (page_id, parent_type, hdate)
				)";
			$ilDB->query($q);
			
			$ilSetting->set("wiki_db", 4);
		}

		if ($wiki_db == 4)
		{
			$q = "ALTER TABLE page_object ADD COLUMN view_cnt int DEFAULT 0";
			$ilDB->query($q);

			$ilSetting->set("wiki_db", 5);
		}

		if ($wiki_db == 5)
		{
			$q = "ALTER TABLE page_object ADD COLUMN last_change TIMESTAMP";
			$ilDB->query($q);
			$q = "ALTER TABLE page_object ADD COLUMN created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
			$ilDB->query($q);

			$ilSetting->set("wiki_db", 6);
		}
	}
}
