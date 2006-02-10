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
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

define("LP_MODE_PERSONAL_DESKTOP",1);
define("LP_MODE_ADMINISTRATION",2);
define("LP_MODE_REPOSITORY",3);
define("LP_MODE_USER_FOLDER",4);

define("LP_ACTIVE_SETTINGS",1);
define("LP_ACTIVE_OBJECTS",2);
define("LP_ACTIVE_PROGRESS",3);

include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

/* Base class for all Learning progress gui classes.
 * Defines modes for presentation according to the context in which it was called
 * E.g: mode LP_MODE_PERSONAL_DESKTOP displays only listOfObjects.
 */

class ilLearningProgressBaseGUI 
{
	var $tpl = null;
	var $ctrl = null;
	var $lng = null;

	var $ref_id = 0;

	var $mode = 0;

	function ilLearningProgressBaseGUI($a_mode,$a_ref_id = 0,$a_usr_id = 0)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('trac');
		$this->tabs_gui =& $ilTabs;

		$this->mode = $a_mode;
		$this->ref_id = $a_ref_id;
		$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
		$this->usr_id = $a_usr_id;

		$this->anonymized = (bool) !ilObjUserTracking::_enabledUserRelatedData();
	}

	function isAnonymized()
	{
		return $this->anonymized;
	}
	
	function getMode()
	{
		return $this->mode;
	}

	function getRefId()
	{
		return $this->ref_id;
	}

	function getObjId()
	{
		return $this->obj_id;
	}

	function getUserId()
	{
		if($this->usr_id)
		{
			return $this->usr_id;
		}
		if((int) $_GET['user_id'])
		{
			return (int) $_GET['user_id'];
		}
		return 0;
	}

	// Protected
	function __getDefaultCommand()
	{
		if(strlen($cmd = $this->ctrl->getCmd()))
		{
			return $cmd;
		}
		return 'show';
	}

	function __setSubTabs($a_active)
	{
		global $rbacsystem;


		
		switch($this->getMode())
		{
			case LP_MODE_PERSONAL_DESKTOP:

				$this->tabs_gui->addSubTabTarget('trac_progress',
												 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
												 "","","",$a_active == LP_ACTIVE_PROGRESS);
				$this->tabs_gui->addSubTabTarget('trac_objects',
												 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
												 "","","",$a_active == LP_ACTIVE_OBJECTS);
				break;


			case LP_MODE_REPOSITORY:

				if($rbacsystem->checkAccess('edit_learning_progress',$this->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget('trac_progress',
											   $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
											   "","","",$a_active == LP_ACTIVE_PROGRESS);
					if(!$this->isAnonymized())
					{
						$this->tabs_gui->addSubTabTarget('trac_objects',
														 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
														 "","","",$a_active == LP_ACTIVE_OBJECTS);
					}
					$this->tabs_gui->addSubTabTarget('trac_settings',
													 $this->ctrl->getLinkTargetByClass('illplistofsettingsgui',''),
													 "","","",$a_active == LP_ACTIVE_SETTINGS);
				}
				break;

			case LP_MODE_ADMINISTRATION:
				
				$this->tabs_gui->addSubTabTarget('trac_progress',
									 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
									 "","","",$a_active == LP_ACTIVE_PROGRESS);
				$this->tabs_gui->addSubTabTarget('trac_objects',
									 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
									 "","","",$a_active == LP_ACTIVE_OBJECTS);
				break;

			case LP_MODE_USER_FOLDER:
				
				// No tabs default class is lpprogressgui
				break;

			default:
				die ('No valid mode given');
				break;
		}

		return true;
	}

	function __buildFooter()
	{
		switch($this->getMode())
		{
			case LP_MODE_PERSONAL_DESKTOP:

				$this->tpl->show(true);
		}
	}

	function __buildHeader()
	{
		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP)
		{
			$this->tpl->setCurrentBlock("header_image");
			$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pd_b.gif"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("HEADER",$this->lng->txt("personal_desktop"));
			
			// set locator
			$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
			$this->tpl->touchBlock("locator_separator");
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("learning_progress"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTargetByClass('illearningprogressgui'));
			$this->tpl->parseCurrentBlock();
		
			// catch feedback message
			sendInfo();
			// display infopanel if something happened
			infoPanel();
		}

	}

	/**
	* insert path
	*/
	function __insertPath(&$a_tpl,$a_ref_id)
	{
		global $tree;

		$path_arr = $tree->getPathFull($a_ref_id);
		$counter = 0;
		foreach($tree->getPathFull($a_ref_id) as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}
		$a_tpl->setCurrentBlock("path_item");
		$a_tpl->setVariable("PATH_ITEM",$path);
		$a_tpl->parseCurrentBlock();

		$a_tpl->setCurrentBlock("path");
		$a_tpl->parseCurrentBlock();

		return $path;
	}

	function __showImageByStatus(&$tpl,$a_status,$tpl_prefix = "")
	{
		switch($a_status)
		{
			case LP_STATUS_IN_PROGRESS:
				$tpl->setVariable($tpl_prefix."STATUS_IMG",ilUtil::getImagePath('scorm/incomplete.gif'));
				$tpl->setVariable($tpl_prefix."STATUS_ALT",$this->lng->txt($a_status));
				break;

			case LP_STATUS_COMPLETED:
				$tpl->setVariable($tpl_prefix."STATUS_IMG",ilUtil::getImagePath('scorm/complete.gif'));
				$tpl->setVariable($tpl_prefix."STATUS_ALT",$this->lng->txt($a_status));
				break;
			
			case LP_STATUS_NOT_ATTEMPTED:
				$tpl->setVariable($tpl_prefix."STATUS_IMG",ilUtil::getImagePath('scorm/running.gif'));
				$tpl->setVariable($tpl_prefix."STATUS_ALT",$this->lng->txt($a_status));
				break;
		}
		return true;
	}


	// Protected Table gui methods
	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}


	/**
	* show details about current object. Uses an existing info_gui object.
	*/
	function __showObjectDetails(&$info,$item_id = 0)
	{
		global $ilObjDataCache;

		$details_id = $item_id ? $item_id : $this->details_id;


		// Section object details
		$info->addSection($this->lng->txt('details'));
		$info->addProperty($this->lng->txt('title'),$ilObjDataCache->lookupTitle($details_id));
		if(strlen($desc = $ilObjDataCache->lookupDescription($details_id)))
		{
			$info->addProperty($this->lng->txt('description'),$desc);
		}
		$info->addProperty($this->lng->txt('trac_mode'),ilLPObjSettings::_mode2Text($this->details_mode));

		if($this->details_mode == LP_MODE_VISITS)
		{
			$info->addProperty($this->lng->txt('trac_required_visits'),ilLPObjSettings::_lookupVisits($details_id));
		}
		
		include_once './Services/MetaData/classes/class.ilMDEducational.php';
		if($seconds = ilMDEducational::_getTypicalLearningTimeSeconds($details_id))
		{
			$info->addProperty($this->lng->txt('meta_typical_learning_time'),ilFormat::_secondsToString($seconds));
		}
	}

	function __appendLPDetails(&$info,$item_id,$user_id)
	{
		global $ilObjDataCache;

		$type = $ilObjDataCache->lookupType($item_id);
		
		// Section learning_progress
		$info->addSection($this->lng->txt('trac_learning_progress'));
	
		switch($type)
		{
			case 'lm':
				include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
				$progress = ilLearningProgress::_getProgress($user_id,$item_id);
			
				if($progress['access_time'])
				{
					$info->addProperty($this->lng->txt('last_access'),date('Y-m-d H:i:s',$progress['access_time']));
				}
				else
				{
					$info->addProperty($this->lng->txt('last_access'),$this->lng->txt('trac_not_accessed'));
				}
				$info->addProperty($this->lng->txt('trac_visits'),(int) $progress['visits']);
				$info->addProperty($this->lng->txt('trac_spent_time'),ilFormat::_secondsToString($progress['spent_time']));
				$info->addProperty($this->lng->txt('trac_status'),$this->lng->txt($this->__readStatus($item_id,$user_id)));
				break;

			case 'tst':
			case 'crs':
			case 'sahs':
				$info->addProperty($this->lng->txt('trac_status'),$this->lng->txt($this->__readStatus($item_id,$user_id)));
				break;

		}

	}

	function __readStatus($a_obj_id,$user_id)
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		if(in_array($user_id,ilLPStatusWrapper::_getInProgress($a_obj_id)))
		{
			return $status = LP_STATUS_IN_PROGRESS;
		}
		elseif(in_array($user_id,ilLPStatusWrapper::_getCompleted($a_obj_id)))
		{
			return $status = LP_STATUS_COMPLETED;
		}
		else
		{
			return $status = LP_STATUS_NOT_ATTEMPTED;
		}
	}

	function __showButton($a_link,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$a_link);
		$this->tpl->setVariable("BTN_TXT",$a_text);
		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}


	/**
	* Function that sorts ids by a given table field using WHERE IN
	* E.g: __sort(array(6,7),'usr_data','lastname','usr_id') => sorts by lastname
	* 
	* @param array Array of ids
	* @param string table name
	* @param string table field
	* @param string id name
	* @return array sorted ids
	*
	* @access protected
	*/
	function __sort($a_ids,$a_table,$a_field,$a_id_name)
	{
		global $ilDB;

		if(!$a_ids)
		{
			return array();
		}

		// use database to sort user array
		$where = "WHERE ".$a_id_name." IN ('";
		$where .= implode("','",$a_ids);
		$where .= "') ";

		$query = "SELECT ".$a_id_name." FROM ".$a_table." ".
			$where.
			"ORDER BY ".$a_field;

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->$a_id_name;
		}
		return $ids ? $ids : array();
	}



}
?>