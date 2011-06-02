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

/**
* Meta Data class
* always instantiate this class first to set/get single meta data elements
*
* @package ilias-core
* @version $Id$
*/

class ilMDBase
{
	/*
	 * object id (NOT ref_id!) of rbac object (e.g for page objects the obj_id
	 * of the content object; for media objects this is set to 0, because their
	 * object id are not assigned to ref ids)
	 */
	var $rbac_id;

	/*
	 * obj_id (e.g for structure objects the obj_id of the structure object)
	 */
	var $obj_id;

	/*
	 * type of the object (e.g st,pg,crs ...)
	 */
	var $obj_type;
	
	/*
	 * export mode, if true, first Identifier will be
	 * set to ILIAS/il_<INSTALL_ID>_<TYPE>_<ID>
	 */
	var $export_mode = false;


	function ilMDBase($a_rbac_id = 0,
					  $a_obj_id = 0,
					  $a_type = 0)
	{
		global $ilDB,$ilLog;

		if ($a_obj_id == 0)
		{
			$a_obj_id = $a_rbac_id;
		}

		$this->db =& $ilDB;
		$this->log =& $ilLog;

		$this->rbac_id = $a_rbac_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_type;
	}

	// SET/GET
	function setRBACId($a_id)
	{
		$this->rbac_id = $a_id;
	}
	function getRBACId()
	{
		return $this->rbac_id;
	}
	function setObjId($a_id)
	{
		$this->obj_id = $a_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}
	function setObjType($a_type)
	{
		$this->obj_type = $a_type;
	}
	function getObjType()
	{
		return $this->obj_type;
	}
	function setMetaId($a_meta_id,$a_read_data = true)
	{
		$this->meta_id = $a_meta_id;

		if($a_read_data)
		{
			$this->read();
		}
	}
	function getMetaId()
	{
		return $this->meta_id;
	}
	function setParentType($a_parent_type)
	{
		$this->parent_type = $a_parent_type;
	}
	function getParentType()
	{
		return $this->parent_type;
	}
	function setParentId($a_id)
	{
		$this->parent_id = $a_id;
	}
	function getParentId()
	{
		return $this->parent_id;
	}
	
	function setExportMode($a_export_mode = true)
	{
		$this->export_mode = $a_export_mode;
	}
	
	function getExportMode()
	{
		return $this->export_mode;
	}


	/*
	 * Should be overwritten in all inherited classes
	 * 
	 * @access public
	 * @return bool
	 */
	function validate()
	{
		return false;
	}

	/*
	 * Should be overwritten in all inherited classes
	 * 
	 * @access public
	 * @return bool
	 */
	function update()
	{
		return false;
	}

	/*
	 * Should be overwritten in all inherited classes
	 * 
	 * @access public
	 * @return bool
	 */
	function save()
	{
		return false;
	}
	/*
	 * Should be overwritten in all inherited classes
	 * 
	 * @access public
	 * @return bool
	 */
	function delete()
	{
	}

	/*
	 * Should be overwritten in all inherited classes
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
	}

}
?>
