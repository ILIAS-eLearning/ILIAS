<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");

/**
* SCORM Resources Element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResources extends ilSCORMObject
{
	var $xml_base;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMResources($a_id = 0)
	{
		global $lng;
		
		parent::ilSCORMObject($a_id);
		$this->setType("srs");

		$this->setTitle($lng->txt("cont_resources"));
	}

	function getXmlBase()
	{
		return $this->xml_base;
	}

	function setXmlBase($a_xml_base)
	{
		$this->xml_base = $a_xml_base;
	}

	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM sc_resources WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setXmlBase($obj_rec["xml_base"]);
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$q = "INSERT INTO sc_resources (obj_id, xml_base) VALUES ".
			"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->getXmlBase()).")";
		$this->ilias->db->query($q);
	}

	function update()
	{
		global $ilDB;
		
		parent::update();

		$q = "UPDATE sc_resources SET ".
			"xml_base = ".$ilDB->quote($this->getXmlBase())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM sc_resources WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);
	}



}
?>
