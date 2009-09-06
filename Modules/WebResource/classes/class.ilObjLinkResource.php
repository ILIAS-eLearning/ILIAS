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

include_once "./classes/class.ilObject.php";

/** @defgroup ModulesWebResource Modules/WebResource
 */

/**
* Class ilObjLinkResource
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilObjLinkResource extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjLinkResource($a_id = 0,$a_call_by_reference = true)
	{
		//$this->type = "lnkr";
		$this->type = "webr";
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	* create object
	* 
	* @param bool upload mode (if enabled no meta data will be created)
	*/
	function create($a_upload = false)
	{
		$new_id = parent::create();
		
		if(!$a_upload)
		{
			$this->createMetaData();
		}
		
		return $new_id;
	}

	/**
	* update object
	*/
	function update()
	{
		$this->updateMetaData();
		parent::update();
	}


	/**
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete items
		include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
		ilLinkResourceItems::_deleteAll($this->getId());


		// Delete notify entries
		include_once './classes/class.ilLinkCheckNotify.php';
		ilLinkCheckNotify::_deleteObject($this->getId());

		// delete meta data
		$this->deleteMetaData();

		return true;
	}

	function initLinkResourceItemsObject()
	{
		include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';

		$this->items_obj =& new ilLinkResourceItems($this->getId());

		return true;
	}
	
	/**
	 * Clone
	 *
	 * @access public
	 * @param int target id
	 * @param int copy id
	 * 
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	$this->cloneMetaData($new_obj);
	 	
	 	// object created now copy other settings
	 	include_once('Modules/WebResource/classes/class.ilLinkResourceItems.php');
	 	$links = new ilLinkResourceItems($this->getId());
	 	$links->cloneItems($new_obj->getId());
	 	
	 	return $new_obj;
	}

	// PRIVATE


} // END class.ilObjLinkResource
?>
