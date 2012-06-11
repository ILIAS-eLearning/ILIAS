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

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <meyer@leifos.com>
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
define("LP_ACTIVE_LM_STATISTICS",4);
define("LP_ACTIVE_USERS",5);
define("LP_ACTIVE_SUMMARY",6);
define("LP_ACTIVE_OBJSTATACCESS",7);
define("LP_ACTIVE_OBJSTATTYPES",8);
define("LP_ACTIVE_OBJSTATDAILY",9);
define("LP_ACTIVE_OBJSTATADMIN",10);

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
	
	var $statistics_activated = false;	// show sub tab for access statistics

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
		$this->obj_type = $ilObjDataCache->lookupType($this->obj_id);
		$this->usr_id = $a_usr_id;

		$this->anonymized = (bool) !ilObjUserTracking::_enabledUserRelatedData();
	}

	function isAnonymized()
	{
		return $this->anonymized;
	}

	function isObjectAnonymized()
	{
		switch($this->obj_type)
		{
			case 'tst':
				include_once './Modules/Test/classes/class.ilObjTest.php';
				if(ilObjTest::_lookupAnonymity($this->obj_id))
				{
					return true;
				}
			default:
				return false;
		}
		return false;
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
	
	function activateStatistics($a_act = true)
	{
		$this->statistics_activated = $a_act;
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
		global $rbacsystem,$ilObjDataCache,$lng,$ilUser;


		
		switch($this->getMode())
		{
			case LP_MODE_PERSONAL_DESKTOP:

				include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
				if(ilObjUserTracking::_hasLearningProgressLearner())
				{
					$this->tabs_gui->addTarget('trac_progress',
													$this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
													"","","",$a_active == LP_ACTIVE_PROGRESS);
				}

				// ownership is also checked by this method
				$types = array("crs", "grp", "exc", "tst", "lm", "sahs", "htlm", "dbk");
				if(ilUtil::_getObjectsByOperations($types, "edit_learning_progress", $ilUser->getId(), 1))
				{
					$this->tabs_gui->addTarget('trac_objects',
													 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
													 "","","",$a_active == LP_ACTIVE_OBJECTS);
				}
				break;


			case LP_MODE_REPOSITORY:

				if($rbacsystem->checkAccess('edit_learning_progress',$this->getRefId()))
				{
					if($this->isAnonymized())
					{
						$this->ctrl->setParameterByClass('illplistofprogressgui','user_id',$this->getUserId());
						$this->tabs_gui->addSubTabTarget('trac_progress',
														 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
														 "","","",$a_active == LP_ACTIVE_PROGRESS);
					}
					else
					{
						// Check if it is a course
						$sub_tab = ($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->getRefId())) == 'crs') ?
							'trac_crs_objects' :
							'trac_objects';

						$this->tabs_gui->addSubTabTarget($sub_tab,
														 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
														 "","","",$a_active == LP_ACTIVE_OBJECTS);

					}

					if ($this->statistics_activated)
					{
						$this->tabs_gui->addSubTabTarget('trac_lm_statistics',
														 $this->ctrl->getLinkTargetByClass('illmstatisticsgui',''),
														 "","","",$a_active == LP_ACTIVE_LM_STATISTICS);
					}

					if(!$this->isAnonymized() && !in_array($this->obj_type, array('tst', 'lm', 'htlm', 'exc')))
					{
						$this->tabs_gui->addSubTabTarget("trac_matrix",
														$this->ctrl->getLinkTargetByClass("illplistofobjectsgui", 'showUserObjectMatrix'),
														"", "", "", $a_active == LP_ACTIVE_MATRIX);
					}

					$this->tabs_gui->addSubTabTarget("trac_summary",
													$this->ctrl->getLinkTargetByClass("illplistofobjectsgui", 'showObjectSummary'),
													"", "", "", $a_active == LP_ACTIVE_SUMMARY);

					$this->tabs_gui->addSubTabTarget('trac_settings',
													 $this->ctrl->getLinkTargetByClass('illplistofsettingsgui',''),
													 "","","",$a_active == LP_ACTIVE_SETTINGS);
				}
				break;

			case LP_MODE_ADMINISTRATION:
				/*
				$this->tabs_gui->addSubTabTarget('trac_progress',
									 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
									 "","","",$a_active == LP_ACTIVE_PROGRESS);
				*/
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
			//$this->tpl->setCurrentBlock("header_image");
			//$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pd_b.gif"));
			//$this->tpl->parseCurrentBlock();
			//$this->tpl->setVariable("HEADER",$this->lng->txt("personal_desktop"));
			$this->tpl->setTitle($this->lng->txt("learning_progress"));
			
			// set locator
/*
			$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
			$this->tpl->touchBlock("locator_separator");
			$this->tpl->touchBlock("locator_item");
			//$this->tpl->setCurrentBlock("locator_item");
			//$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
			//$this->tpl->setVariable("LINK_ITEM",
			//						$this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
			//$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("learning_progress"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTargetByClass('illearningprogressgui'));
			$this->tpl->parseCurrentBlock();
*/
		
			// display infopanel if something happened
			ilUtil::infoPanel();
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
		return ilLearningProgressBaseGUI::_showImageByStatus($tpl,$a_status,$tpl_prefix);
	}
	
	// we need this public in table classes
	public static function _showImageByStatus(&$tpl,$a_status,$tpl_prefix = "")
	{
		global $lng;
		
		$tpl->setVariable($tpl_prefix."STATUS_IMG",
			ilLearningProgressBaseGUI::_getImagePathForStatus($a_status));
		$tpl->setVariable($tpl_prefix."STATUS_ALT",$lng->txt($a_status));
		
		return true;
	}
	
	/**
	 * Get image path for status
	 */
	function _getImagePathForStatus($a_status)
	{
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");

		// constants are either number or string, so make comparison string-based
		switch((string)$a_status)
		{
			case LP_STATUS_IN_PROGRESS_NUM:
			case LP_STATUS_IN_PROGRESS:
			case LP_STATUS_REGISTERED:
				return ilUtil::getImagePath('scorm/incomplete.gif');
				break;

			case LP_STATUS_COMPLETED_NUM:
			case LP_STATUS_COMPLETED:
			case LP_STATUS_PARTICIPATED:
				return ilUtil::getImagePath('scorm/complete.gif');
				break;
			
			case LP_STATUS_NOT_ATTEMPTED:
			case LP_STATUS_NOT_PARTICIPATED:
			case LP_STATUS_NOT_REGISTERED:
				return ilUtil::getImagePath('scorm/not_attempted.gif');
				break;

			case LP_STATUS_FAILED_NUM:
			case LP_STATUS_FAILED:
				return ilUtil::getImagePath('scorm/failed.gif');
				break;
			
			default:
				return ilUtil::getImagePath('scorm/not_attempted.gif');
				break;
		}		
	}

	/**
	 * Get status alt text
	 */
	function _getStatusText($a_status)
	{
		global $lng;
		
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
//echo "#".$a_status."#";
		switch($a_status)
		{
			case LP_STATUS_IN_PROGRESS_NUM:
				return $lng->txt(LP_STATUS_IN_PROGRESS);
				
			case LP_STATUS_COMPLETED_NUM:
				return $lng->txt(LP_STATUS_COMPLETED);

			case LP_STATUS_FAILED_NUM:
				return $lng->txt(LP_STATUS_FAILED);

			default:
				if ($a_status === LP_STATUS_NOT_ATTEMPTED_NUM)
				{
					return $lng->txt(LP_STATUS_NOT_ATTEMPTED);
				}
				return $lng->txt($a_status);
		}		
	}


	// Protected Table gui methods
	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}


	/**
	* show details about current object. Uses an existing info_gui object.
	*/
	function __showObjectDetails(&$info,$item_id = 0)
	{
		global $ilObjDataCache;

		$details_id = $item_id ? $item_id : $this->details_id;

		include_once './Services/MetaData/classes/class.ilMDEducational.php';
		if(ilLPObjSettings::_lookupMode($details_id) == LP_MODE_VISITS ||
		   ilMDEducational::_getTypicalLearningTimeSeconds($details_id))
		{

			// Section object details
			$info->addSection($this->lng->txt('details'));

			/*
			$info->addProperty($this->lng->txt('title'),$ilObjDataCache->lookupTitle($details_id));

			// :TODO: event title

			if(strlen($desc = $ilObjDataCache->lookupDescription($details_id)))
			{
				$info->addProperty($this->lng->txt('description'),$desc);
			}
			$info->addProperty($this->lng->txt('trac_mode'),ilLPObjSettings::_mode2Text(ilLPObjSettings::_lookupMode($details_id)));
			*/

			if(ilLPObjSettings::_lookupMode($details_id) == LP_MODE_VISITS)
			{
				$info->addProperty($this->lng->txt('trac_required_visits'),ilLPObjSettings::_lookupVisits($details_id));
			}

			if($seconds = ilMDEducational::_getTypicalLearningTimeSeconds($details_id))
			{
				$info->addProperty($this->lng->txt('meta_typical_learning_time'),ilFormat::_secondsToString($seconds));
			}

			return true;
		}
		return false;
	}

	function __appendUserInfo(&$info, $a_user)
	{
		global $ilUser;

		if(!is_object($a_user))
		{
			$a_user = ilObjectFactory::getInstanceByObjId($a_user);
		}

		if($a_user->getId() != $ilUser->getId())
		{
			$info->addSection($this->lng->txt("trac_user_data"));
			// $info->addProperty($this->lng->txt('username'),$a_user->getLogin());
			// $info->addProperty($this->lng->txt('name'),$a_user->getFullname());
			$info->addProperty($this->lng->txt('last_login'),
				ilDatePresentation::formatDate(new ilDateTime($a_user->getLastLogin(),IL_CAL_DATETIME)));
			$info->addProperty($this->lng->txt('trac_total_online'),
							   ilFormat::_secondsToString(ilOnlineTracking::_getOnlineTime($a_user->getId())));
		   return true;
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
			case 'htlm':
				include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
				$progress = ilLearningProgress::_getProgress($user_id,$item_id);
			
				if($progress['access_time'])
				{
					$info->addProperty($this->lng->txt('last_access'),
						ilDatePresentation::formatDate(new ilDateTime($progress['access_time'],IL_CAL_DATETIME)));
				}
				else
				{
					$info->addProperty($this->lng->txt('last_access'),$this->lng->txt('trac_not_accessed'));
				}
				$info->addProperty($this->lng->txt('trac_visits'),(int) $progress['visits']);
				if($type == 'lm')
				{
					$info->addProperty($this->lng->txt('trac_spent_time'),ilFormat::_secondsToString($progress['spent_seconds']));
				}
				
				// display status as image
				include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
				$status = $this->__readStatus($item_id,$user_id);
				$status_path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
				$status_text = ilLearningProgressBaseGUI::_getStatusText($status);
				$info->addProperty($this->lng->txt('trac_status'), 
					ilUtil::img($status_path, $status_text)." ".$status_text);
				break;

			case 'exc':
			case 'tst':
			case 'crs':
			case 'sahs':
			case 'grp':
				// display status as image
				include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
				$status = $this->__readStatus($item_id,$user_id);
				$status_path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
				$status_text = ilLearningProgressBaseGUI::_getStatusText($status);
				$info->addProperty($this->lng->txt('trac_status'), 
					ilUtil::img($status_path, $status_text)." ".$status_text);
				break;

		}

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		if(strlen($mark = ilLPMarks::_lookupMark($user_id,$item_id)))
		{
			$info->addProperty($this->lng->txt('trac_mark'),$mark);
		}
		if(strlen($comment = ilLPMarks::_lookupComment($user_id,$item_id)))
		{
			$info->addProperty($this->lng->txt('trac_comment'),$comment);
		}
	}

	function __readStatus($a_obj_id,$user_id)
	{
		include_once 'Services/Tracking/classes/class.ilLPStatus.php';
		$status = ilLPStatus::_lookupStatus($a_obj_id, $user_id);

		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		switch($status)
		{
			case LP_STATUS_IN_PROGRESS_NUM:
				return LP_STATUS_IN_PROGRESS;

			case LP_STATUS_COMPLETED_NUM:
				return LP_STATUS_COMPLETED;

			case LP_STATUS_FAILED_NUM:
				return LP_STATUS_FAILED;

			case LP_STATUS_NOT_ATTEMPTED_NUM:
				return LP_STATUS_NOT_ATTEMPTED;

			default:
				return $status;
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

		// comment by mjansen: Requesting database in gui classes?

		// use database to sort user array
		$where = "WHERE ".$ilDB->in($a_id_name, $a_ids, false, 'integer')." ";

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

	function __getPercent($max,$reached)
	{
		if(!$max)
		{
			return "0%";
		}

		return sprintf("%d%%",$reached / $max * 100);
	}

	function __readItemStatusInfo($a_items)
	{
		global $ilObjDataCache;

		foreach($a_items as $item_id)
		{
			$this->obj_data[$item_id]['mode'] = ilLPObjSettings::_lookupMode($item_id);
			$this->obj_data[$item_id]['type'] = $ilObjDataCache->lookupType($item_id);
			if($this->obj_data[$item_id]['mode'] == LP_MODE_TLT)
			{
				include_once './Services/MetaData/classes/class.ilMDEducational.php';
				$this->obj_data[$item_id]['tlt'] = ilMDEducational::_getTypicalLearningTimeSeconds($item_id);
			}
			if($this->obj_data[$item_id]['mode'] == LP_MODE_VISITS)
			{
				include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
				$this->obj_data[$item_id]['visits'] = ilLPObjSettings::_lookupVisits($item_id);
			}
			if($this->obj_data[$item_id]['mode'] == LP_MODE_SCORM)
			{
				include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
				$this->obj_data[$item_id]['scos'] = count(ilLPCollectionCache::_getItems($item_id));
			}
		}
	}

	function __getLegendHTML()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.lp_legend.html", true, true, "Services/Tracking");
		$tpl->setVariable("IMG_NOT_ATTEMPTED",
			ilUtil::getImagePath("scorm/not_attempted.gif"));
		$tpl->setVariable("IMG_IN_PROGRESS",
			ilUtil::getImagePath("scorm/incomplete.gif"));
		$tpl->setVariable("IMG_COMPLETED",
			ilUtil::getImagePath("scorm/completed.gif"));
		$tpl->setVariable("IMG_FAILED",
			ilUtil::getImagePath("scorm/failed.gif"));
		$tpl->setVariable("TXT_NOT_ATTEMPTED",
			$lng->txt("trac_not_attempted"));
		$tpl->setVariable("TXT_IN_PROGRESS",
			$lng->txt("trac_in_progress"));
		$tpl->setVariable("TXT_COMPLETED",
			$lng->txt("trac_completed"));
		$tpl->setVariable("TXT_FAILED",
			$lng->txt("trac_failed"));
		return $tpl->get();
	}

	function __showEditUser($a_user_id, $a_ref_id, $a_cancel, $a_sub_id = false)
	{
		global $ilObjDataCache, $lng, $ilCtrl;

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		if(!$a_sub_id)
        {
			$obj_id = $ilObjDataCache->lookupObjId($a_ref_id);
		}
		else
		{
			$ilCtrl->setParameter($this,'userdetails_id',$a_sub_id);
			$obj_id = $ilObjDataCache->lookupObjId($a_sub_id);
		}
		
		$marks = new ilLPMarks($obj_id, $a_user_id);

		$tpl = new ilTemplate('tpl.lp_edit_user.html', true, true, 'Services/Tracking');

        $tpl->setVariable("OBJ_TITLE", $lng->txt("edit").": ".$ilObjDataCache->lookupTitle($obj_id));
		$tpl->setVariable("OBJ_SUBTITLE", $this->lng->txt('trac_mode').": ".ilLPObjSettings::_mode2Text(ilLPObjSettings::_lookupMode($obj_id)));

		$ilCtrl->setParameter($this,'user_id',$a_user_id);
		$ilCtrl->setParameter($this,'details_id',$a_ref_id);
		$tpl->setVariable("FORMACTION",$ilCtrl->getFormAction($this));

		$tpl->setVariable("TYPE_IMG",ilObjUser::_getPersonalPicturePath($a_user_id,'xxsmall'));
		$tpl->setVariable("ALT_IMG",$ilObjDataCache->lookupTitle($a_user_id));
		$tpl->setVariable("TXT_LP",$lng->txt('trac_learning_progress_tbl_header'));

		$tpl->setVariable("COMMENT",ilUtil::prepareFormOutput($marks->getComment(),false));

		$type = $ilObjDataCache->lookupType($obj_id);
		if($type != 'lm')
		{
			$tpl->setVariable("TXT_MARK",$lng->txt('trac_mark'));
			$tpl->setVariable("MARK",ilUtil::prepareFormOutput($marks->getMark(),false));
		}

		$tpl->setVariable("TXT_COMMENT",$lng->txt('trac_comment'));

		$mode = ilLPObjSettings::_lookupMode($obj_id);
		if($mode == LP_MODE_MANUAL or $mode == LP_MODE_MANUAL_BY_TUTOR)
		{
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			$completed = ilLPStatus::_lookupStatus($obj_id, $a_user_id);		

			$tpl->setVariable("mode_manual");
			$tpl->setVariable("TXT_COMPLETED",$lng->txt('trac_completed'));
			$tpl->setVariable("CHECK_COMPLETED",ilUtil::formCheckbox(($completed == LP_STATUS_COMPLETED_NUM),
																		   'completed',
																		   '1'));
		}

		$tpl->setVariable("TXT_CANCEL",$lng->txt('cancel'));
		$tpl->setVariable("TXT_SAVE",$lng->txt('save'));
		$tpl->setVariable("CMD_CANCEL", $a_cancel);

		return $tpl->get();
	}

	function __updateUser($user_id, $obj_id)
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		$marks = new ilLPMarks($obj_id, $user_id);
		$marks->setMark(ilUtil::stripSlashes($_POST['mark']));
		$marks->setComment(ilUtil::stripSlashes($_POST['comment']));
		$marks->setCompleted((bool) $_POST['completed']);
		$marks->update();

		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_updateStatus($obj_id, $user_id);
	}
	
	static function isObjectOffline($a_obj_id, $a_type = null)
	{
		global $objDefinition, $ilObjDataCache;

		if(!$a_type)
		{
			$a_type = $ilObjDataCache->lookupType($a_obj_id);
		}

		$class = "ilObj".$objDefinition->getClassName($a_type)."Access";
		include_once $objDefinition->getLocation($a_type)."/class.".$class.".php";

		return call_user_func(array($class,'_isOffline'), $a_obj_id);

		// PHP 5.3 only ?
		//return $class::_isOffline($obj_id);
	}
}

?>