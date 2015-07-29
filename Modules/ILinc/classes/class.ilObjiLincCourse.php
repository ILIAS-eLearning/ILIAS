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
* Class ilObjiLincCourse
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
*
* @version $Id$
*
* @extends ilObject
*/

require_once 'Services/Container/classes/class.ilContainer.php';

class ilObjiLincCourse extends ilContainer
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function ilObjiLincCourse($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = 'icrs';
		$this->ilObject($a_id,$a_call_by_reference);
		$this->setRegisterMode(false);
	}
	
	public function getViewMode()
	{
		return ilContainer::VIEW_BY_TYPE;
	}
	
	/**
	* 
	* @access private
	*/
	function read()
	{
		parent::read();
		return true;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		parent::update();
		return true;
	}
	
	/**
	* create course on iLinc server
	*
	* @access	public
	* @return	boolean
	*/
	function addCourse()
	{
		return true;
	}
	

	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		parent::delete();
		return true;
	}
	
	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		return true;
	}
	
	function getSubItems()
	{
		return array();
	}
}