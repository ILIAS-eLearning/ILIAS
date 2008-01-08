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
* SCORM Manifest
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMManifest extends ilSCORMObject
{
	var $import_id;
	var $version;
	var $xml_base;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMManifest($a_id = 0)
	{
		parent::ilSCORMObject($a_id);
		$this->setType("sma");
	}

	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
		$this->setTitle($a_import_id);
	}

	function getVersion()
	{
		return $this->version;
	}

	function setVersion($a_version)
	{
		$this->version = $a_version;
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

		$q = "SELECT * FROM sc_manifest WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(MDB2_FETCHMODE_ASSOC);
		$this->setImportId($obj_rec["import_id"]);
		$this->setVersion($obj_rec["version"]);
		$this->setXmlBase($obj_rec["xml_base"]);
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$q = "INSERT INTO sc_manifest (obj_id, import_id, version, xml_base) VALUES ".
			"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->getImportId()).
			", ".$ilDB->quote($this->getVersion()).", ".$ilDB->quote($this->getXmlBase()).")";
		$this->ilias->db->query($q);
	}

	function update()
	{
		global $ilDB;
		
		parent::update();

		$q = "UPDATE sc_manifest SET ".
			"import_id = ".$ilDB->quote($this->getImportId()).", ".
			"version = ".$ilDB->quote($this->getVersion()).", ".
			"xml_base = ".$ilDB->quote($this->getXmlBase())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM sc_manifest WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);
	}

}
?>
