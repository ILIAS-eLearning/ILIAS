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
* SCORM Organizations
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganizations extends ilSCORMObject
{
	var $default_organization;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMOrganizations($a_id = 0)
	{
		global $lng;

		// title should be overrriden by ilSCORMExplorer
		$this->setTitle($lng->txt("cont_organizations"));

		parent::ilSCORMObject($a_id);
		$this->setType("sos");
	}

	function getDefaultOrganization()
	{
		return $this->default_organization;
	}

	function setDefaultOrganization($a_def_org)
	{
		$this->default_organization = $a_def_org;
	}

	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM sc_organizations WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setDefaultOrganization($obj_rec["default_organization"]);
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$q = "INSERT INTO sc_organizations (obj_id, default_organization) VALUES ".
			"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->getDefaultOrganization()).")";
		$this->ilias->db->query($q);
	}

	function update()
	{
		global $ilDB;
		
		parent::update();

		$q = "UPDATE sc_organizations SET ".
			"default_organization = ".$ilDB->quote($this->getDefaultOrganization())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM sc_organizations WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);
	}

}
?>
