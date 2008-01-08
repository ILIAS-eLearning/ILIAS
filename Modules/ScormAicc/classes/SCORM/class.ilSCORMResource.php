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
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceFile.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceDependency.php");

/**
* SCORM Resource
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResource extends ilSCORMObject
{
	var $import_id;
	var $resourcetype;
	var $scormtype;
	var $href;
	var $xml_base;
	var $files;
	var $dependencies;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMResource($a_id = 0)
	{
		$this->files = array();
		$this->dependencies = array();
		$this->setType("sre");
		parent::ilSCORMObject($a_id);

	}

	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}

	function getResourceType()
	{
		return $this->resourcetype;
	}

	function setResourceType($a_type)
	{
		$this->resourcetype = $a_type;
	}

	function getScormType()
	{
		return $this->scormtype;
	}

	function setScormType($a_scormtype)
	{
		$this->scormtype = $a_scormtype;
	}

	function getHRef()
	{
		return $this->href;
	}

	function setHRef($a_href)
	{
		$this->href = $a_href;
		$this->setTitle($a_href);
	}

	function getXmlBase()
	{
		return $this->xml_base;
	}

	function setXmlBase($a_xml_base)
	{
		$this->xml_base = $a_xml_base;
	}

	function addFile(&$a_file_obj)
	{
		$this->files[] =& $a_file_obj;
	}

	function &getFiles()
	{
		return $this->files;
	}

	function addDependency(&$a_dependency)
	{
		$this->dependencies[] =& $a_dependency;
	}

	function &getDependencies()
	{
		return $this->dependencies;
	}

	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM sc_resource WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setImportId($obj_rec["import_id"]);
		$this->setResourceType($obj_rec["resourcetype"]);
		$this->setScormType($obj_rec["scormtype"]);
		$this->setHRef($obj_rec["href"]);
		$this->setXmlBase($obj_rec["xml_base"]);

		// read files
		$q = "SELECT * FROM sc_resource_file WHERE res_id = ".$ilDB->quote($this->getId()).
			" ORDER BY nr";
		$file_set = $this->ilias->db->query($q);
		while ($file_rec = $file_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$res_file =& new ilSCORMResourceFile();
			$res_file->setHref($file_rec["href"]);
			$this->addFile($res_file);
		}
		// read dependencies
		$q = "SELECT * FROM sc_resource_dependency WHERE res_id = ".$ilDB->quote($this->getId()).
			" ORDER BY nr";
		$dep_set = $this->ilias->db->query($q);
		while ($dep_rec = $dep_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$res_dep =& new ilSCORMResourceDependency();
			$res_dep->setIdentifierRef($dep_rec["identifierref"]);
			$this->addDependency($res_dep);
		}
	}

	function readByIdRef($a_id_ref, $a_slm_id)
	{
		global $ilBench, $ilDB;
		
		$ilBench->start("SCORMResource", "readByIdRef_Query");
		
		$q = "SELECT ob.obj_id AS id FROM sc_resource AS res, scorm_object as ob ".
		"WHERE ob.obj_id = res.obj_id ".
		"AND res.import_id = ".$ilDB->quote($a_id_ref)." ".
		"AND ob.slm_id = ".$ilDB->quote($a_slm_id);

		$id_set = $this->ilias->db->query($q);
		$ilBench->stop("SCORMResource", "readByIdRef_Query");
		
		if ($id_rec = $id_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->setId($id_rec["id"]);
			$this->read();
		}
	}

	function _lookupIdByIdRef($a_id_ref, $a_slm_id)
	{
		global $ilBench, $ilDB;
		
		$q = "SELECT ob.obj_id AS id FROM sc_resource AS res, scorm_object as ob ".
		"WHERE ob.obj_id = res.obj_id ".
		"AND res.import_id = ".$ilDB->quote($a_id_ref)." ".
		"AND ob.slm_id = ".$ilDB->quote($a_slm_id);

		$id_set = $ilDB->query($q);
		if ($id_rec = $id_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $id_rec["id"];
		}
		return 0;
	}
	
	function _lookupScormType($a_obj_id)
	{
		global $ilDB;
		
		$q = "SELECT scormtype FROM sc_resource WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$st_set = $ilDB->query($q);
		if ($st_rec = $st_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $st_rec["scormtype"];
		}
		return "";
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$q = "INSERT INTO sc_resource (obj_id, import_id, resourcetype, scormtype, href, ".
			"xml_base) VALUES ".
			"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->getImportId()).",".
			$ilDB->quote($this->getResourceType()).",".$ilDB->quote($this->getScormType()).",".
			$ilDB->quote($this->getHref()).
			",".$ilDB->quote($this->getXmlBase()).")";
		$this->ilias->db->query($q);

		// save files
		for($i=0; $i<count($this->files); $i++)
		{
			$q = "INSERT INTO sc_resource_file (res_id, href, nr) VALUES ".
				"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->files[$i]->getHref()).
				",".$ilDB->quote(($i + 1)).")";
			$this->ilias->db->query($q);
		}

		// save dependencies
		for($i=0; $i<count($this->dependencies); $i++)
		{
			$q = "INSERT INTO sc_resource_dependency (res_id, identifierref, nr) VALUES ".
				"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->dependencies[$i]->getIdentifierRef()).
				",".$ilDB->quote(($i + 1)).")";
			$this->ilias->db->query($q);
		}
	}

	function update()
	{
		global $ilDB;
		
		parent::update();

		$q = "UPDATE sc_resource SET ".
			"import_id = ".$ilDB->quote($this->getImportId()).", ".
			"resourcetype = ".$ilDB->quote($this->getResourceType()).", ".
			"scormtype = ".$ilDB->quote($this->getScormType()).", ".
			"href = ".$ilDB->quote($this->getHRef()).", ".
			"xml_base = ".$ilDB->quote($this->getXmlBase())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

		// save files
		$q = "DELETE FROM sc_resource_file WHERE res_id = ".
			$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
		for($i=0; $i<count($this->files); $i++)
		{
			$q = "INSERT INTO sc_resource_file (res_id, href, nr) VALUES ".
				"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->files[$i]->getHref()).
				",".$ilDB->quote(($i + 1)).")";
			$this->ilias->db->query($q);
		}

		// save dependencies
		$q = "DELETE FROM sc_resource_dependency WHERE res_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
		for($i=0; $i<count($this->dependencies); $i++)
		{
			$q = "INSERT INTO sc_resource_dependency (res_id, identifierref, nr) VALUES ".
				"(".$ilDB->quote($this->getId()).", ".$ilDB->quote($this->dependencies[$i]->getIdentifierRef()).
				",".$ilDB->quote(($i + 1)).")";
			$this->ilias->db->query($q);
		}
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM sc_resource WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);

		$q = "DELETE FROM sc_resource_file WHERE res_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);

		$q = "DELETE FROM sc_resource_dependency WHERE res_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);
	}


}
?>
