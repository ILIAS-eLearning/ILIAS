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
* Class ilContainer
*
* XHTML Page class
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/

class ilXHTMLPage
{
	var $id = 0;
	var $content = "";

	/**
	* Constructor
	*/
	function ilXHTMLPage($a_id = 0)
	{
		if ($a_id > 0)
		{
			$this->setId($a_id);
			$this->read();
		}
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getContent()
	{
		return $this->content;
	}
	
	function setContent($a_content)
	{
		$this->content = $a_content;
	}

	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM xhtml_page WHERE id = ".
			$ilDB->quote($this->getId()));
		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->setContent($rec["content"]);
		}
	}
	
	function save()
	{
		global $ilDB;
		
		if ($this->getId() > 0)
		{
			$ilDB->query("UPDATE xhtml_page SET ".
				"content = ".$ilDB->quote($this->getContent()).
				" WHERE id = ".$ilDB->quote($this->getId()));
		}
		else
		{
			$ilDB->query("INSERT INTO xhtml_page (content) VALUES ".
				"(".$ilDB->quote($this->getContent()).")");
			$this->setId($ilDB->getLastInsertId());
		}
	}
}
?>
