<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObjiLincClassroom
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

include_once ('./classes/class.ilObject.php');
include_once ('class.ilnetucateXMLAPI.php');

class ilObjiLincClassroom extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjiLincClassroom($a_icla_id,$a_icrs_id)
	{
		global $ilErr,$ilias,$lng;
		
		$this->type = "icla";
		$this->id = $a_icla_id;
		$this->parent = $a_icrs_id;
		$this->ilinc = new ilnetucateXMLAPI();

		$this->ilErr =& $ilErr;
		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->max_title = MAXLENGTH_OBJ_TITLE;
		$this->max_desc = MAXLENGTH_OBJ_DESC;
		$this->add_dots = true;

		$this->referenced = false;
		$this->call_by_reference = false;

		if (!empty($this->id))
		{
			$this->read();
		}
		
		return $this;
	}
	
	function _lookupiCourseId($a_ref_id)
	{
		global $ilDB;

		$q = "SELECT course_id FROM ilinc_data ".
			 "LEFT JOIN object_reference ON object_reference.obj_id=ilinc_data.obj_id ".
			 "WHERE object_reference.ref_id = '".$a_ref_id."'";
		$obj_set = $ilDB->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["course_id"];
	}
	
	/**
	* 
	* @access private
	*/
	function read()
	{
		$this->ilinc->findClass($this->id);
		$response = $this->ilinc->sendRequest();

		if ($response->isError())
		{
			$this->ilErr->raiseError($response->getErrorMsg(),$this->ilErr->MESSAGE);
		}
		
		$this->setTitle($response->data['classes'][$this->id]['name']);
		$this->setDescription($response->data['classes'][$this->id]['description']);
		
		// TODO: fetch instructor user if assigned

	}
	
	function saveID($a_icla_id,$a_icrs_id)
	{
		$q = "INSERT INTO ilinc_data (obj_id,type,course_id,class_id) VALUES (".$this->id.",'icla','".$a_icrs_id."','".$a_icla_id."')";
		$this->ilias->db->query($q);
	}
	
	function joinClass(&$a_user_obj,$a_ilinc_class_id)
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->joinClass($a_user_obj,$a_ilinc_class_id);
		$response = $ilinc->sendRequest("joinClass");
		
		if ($response->isError())
		{
			$this->ilias->raiseError($response->getErrorMsg(),$this->ilias->MESSAGE);
		}
		
		//var_dump($response->data);exit;
		
		// return URL to join class room
		return $response->data['url']['cdata'];
	}
	
	function findUser(&$a_user_obj)
	{
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->findUser($a_user_obj);
		$response = $ilinc->sendRequest();
		
		var_dump($response->data);
		exit;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		$this->ilinc->editClass($this->id,array("name" => $this->getTitle(),"description" => $this->getDescription()));
		$response = $this->ilinc->sendRequest();

		if ($response->isError())
		{
			$this->ilErr->raiseError($response->getErrorMsg(),$this->ilErr->MESSAGE);
		}
		
		return true;
	}
	
	/**
	* copy all entries of your object.
	* 
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// get object instance of cloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");		

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		$this->ilinc->removeClass($this->id);
		$response = $this->ilinc->sendRequest();

		if ($response->isError())
		{
			return $response->getErrorMsg();
		}
		
		return $response->data['result']['cdata'];
	}

} // END class.ilObjiLincClassroom
?>
