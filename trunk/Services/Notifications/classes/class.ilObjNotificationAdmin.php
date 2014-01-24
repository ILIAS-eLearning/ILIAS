<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesNotifications Services/Notifications
 */

/**
* Class ilObjNotificationAdmin
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
* @ingroup ModulesNotification
*/
class ilObjNotificationAdmin extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0,$a_call_by_reference = true) {
            $this->type = 'nota';
            $this->ilObject($a_id,$a_call_by_reference);

	}

	/**
	* create object
	* 
	* @param bool upload mode (if enabled no entries in file_data will be done)
	*/
	function create() {
		return parent::create();
	}

	/**
	* delete file and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;
        }

} // END class.ilObjFile
?>
