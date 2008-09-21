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

include_once "./Modules/Course/classes/class.ilObjCourseListGUI.php";

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ModulesCourseReference
*/
class ilObjCourseReferenceListGUI extends ilObjCourseListGUI
{
	protected $reference_obj_id = null;
	protected $reference_ref_id = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct()
	{
	 	parent::__construct();
	}
	
	public function getIconImageType() 
	{
		return 'crsr';
	}
	
	/**
	 * get command id
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getCommandId()
	{
		return $this->reference_ref_id;
	}
	
	/**
	* initialisation
	*/
	function init()
	{
		$this->static_link_enabled = false;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = true;
		$this->info_screen_enabled = false;
		$this->type = "crs";
		$this->gui_class_name = "ilobjcoursegui";
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}

		// general commands array
		include_once('./Modules/CourseReference/classes/class.ilObjCourseReferenceAccess.php');
		$this->commands = ilObjCourseReferenceAccess::_getCommands();
	}
	
	
	
	/**
	* inititialize new item
	* Course reference inits the course item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		global $ilBench;
		
		$this->reference_ref_id = $a_ref_id;
		$this->reference_obj_id = $a_obj_id;
		
		include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
		$target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
		
		$target_ref_ids = ilObject::_getAllReferences($target_obj_id);
		$target_ref_id = current($target_ref_ids);
		$target_title = ilObject::_lookupTitle($target_obj_id);
		$target_description = ilObject::_lookupDescription($target_obj_id);
		
		$ilBench->start("ilObjCourseListGUI", "1000_checkAllConditions");
		$this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($target_ref_id,$target_obj_id);
		$ilBench->stop("ilObjCourseListGUI", "1000_checkAllConditions");
		
		
		parent::initItem($target_ref_id, $target_obj_id,$target_title,$target_description);

//echo "A-".memory_get_usage();echo "-".$full_class;
//echo "B-".memory_get_usage();echo "-".$full_class;
	}
	
	/**
	 * get command link
	 *
	 * @access public
	 * @param string $a_cmd
	 * @return
	 */
	public function getCommandLink($a_cmd)
	{
		switch($a_cmd)
		{
			case '':
			case 'view':
				return 'repository.php?ref_id='.$this->ref_id.'&cmd='.$a_cmd;
				
			default:
				return 'repository.php?ref_id='.$this->getCommandId().'&cmd='.$a_cmd;
		}
	}
	

}
?>