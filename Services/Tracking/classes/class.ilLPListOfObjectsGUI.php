<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
include_once 'Services/Search/classes/class.ilUserFilterGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjectsTableGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilPDFPresentation, ilLPObjectsTableGUI, ilTrUserObjectsPropsTableGUI, ilTrSummaryTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		global $ilUser,$ilObjDataCache;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
		// $this->__checkPDF();

		// Set item id for details
		$this->__initDetails((int) $_REQUEST['details_id']);
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser;

		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'iltruserobjectspropstablegui':
				$user_id = (int)$_GET["user_id"];
				$this->ctrl->setParameter($this, "user_id", $user_id);

				$this->ctrl->setParameter($this, "details_id", $this->details_id);

				include_once("./Services/Tracking/classes/class.ilTrUserObjectsPropsTableGUI.php");
				$table_gui = new ilTrUserObjectsPropsTableGUI($this, "userDetails", "truop".$user_id,
					$user_id, $this->details_obj_id, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;
			
			case 'illpobjectstablegui':
				include_once './Services/Tracking/classes/class.ilLPObjectsTableGUI.php';
			    $table_gui = new ilLPObjectsTableGUI($this, "", $this->tracked_user);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrsummarytablegui':
				include_once './Services/Tracking/classes/class.ilTrSummaryTableGUI.php';
			    $table_gui = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			/*
			case 'ilpdfpresentation':
				include_once './Services/Tracking/classes/class.ilPDFPresentation.php';
				$pdf_gui = new ilPDFPresentation($this->getMode(),$this->details_id,$this->getUserId());
				$pdf_gui->setType(LP_ACTIVE_OBJECTS);
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($pdf_gui);
				break;
            */

			default:
			    $cmd = $this->__getDefaultCommand();
				$this->$cmd();
		}

		return true;
	}

	function updateUser()
	{
		if(isset($_GET["userdetails_id"]))
		{
			$parent = $this->details_id;
			$this->__initDetails((int)$_GET["userdetails_id"]);
		}
		
		$this->__updateUser($_REQUEST['user_id'], $this->details_obj_id);
		ilUtil::sendSuccess($this->lng->txt('trac_update_edit_user'));

		if(!isset($_GET["userdetails_id"]))
		{
			$this->details();
		}
		else
		{
			$this->__initDetails($parent);
			$this->userDetails();
		}
	}

	function editUser()
	{
		global $ilObjDataCache;

		$parent_id = $this->details_id;
		if(isset($_GET["userdetails_id"]))
		{
			$this->__initDetails((int)$_GET["userdetails_id"]);
			$sub_id = $this->details_id;
			$cancel = "userdetails";
		}
		else
		{
			$sub_id = NULL;
			$cancel = "details";
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->__appendUserInfo($info, (int)$_GET['user_id']);
		// $this->__appendLPDetails($info,$this->details_obj_id,(int)$_GET['user_id']);

		$this->tpl->setVariable("ADM_CONTENT", $info->getHTML().$this->__showEditUser((int)$_GET['user_id'], $parent_id, $cancel, $sub_id));
	}

	function details()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		// Show back button
		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP or
		   $this->getMode() == LP_MODE_ADMINISTRATION)
		{
			$this->__showButton($this->ctrl->getLinkTarget($this,'show'),$this->lng->txt('trac_view_list'));
		}

		/*
		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createDetails'),$this->lng->txt('pdf_export'));
		}
		 */

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());

		$this->__showUsersList();
	}

	function __showUsersList()
	{
		$this->obj_tpl = new ilTemplate('tpl.lp_loo_user_list.html',true,true,'Services/Tracking');
       
		$this->obj_tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->obj_tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_usr'));
		$this->obj_tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('trac_usr_list'));

		// Show table header
		$this->obj_tpl->setVariable("HEAD_STATUS",$this->lng->txt('trac_status'));
		$this->obj_tpl->setVariable("HEAD_MARK",$this->lng->txt('trac_mark'));
		$this->obj_tpl->setVariable("HEAD_OPTIONS",$this->lng->txt('actions'));

		// Show timings header
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		if($this->has_timings = ilCourseItems::_hasCollectionTimings($this->details_id))
		{
			$this->obj_tpl->setVariable('HEAD_TIMING',$this->lng->txt('trac_head_timing'));
			$this->obj_tpl->setVariable('HEAD_TIME_PASSED',$this->lng->txt('trac_time_passed'));
		}

		$this->ctrl->setParameter($this, "details_id", $this->details_id);

		include_once "Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php";
		$gui = new ilTrObjectUsersPropsTableGUI($this, "details", "objectdetails".$this->details_obj_id, $this->details_obj_id, $this->details_id);
		
		$this->tpl->setVariable("LP_OBJECTS", $gui->getHTML());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
	}

	function userDetails()
	{
		global $ilCtrl, $ilObjDataCache;

		$ilCtrl->setParameter($this, "details_id", $this->details_id);

		// Show back button
		$this->__showButton($this->ctrl->getLinkTarget($this,'details'), $this->lng->txt('trac_view_list'));

		$user_id = (int)$_GET["user_id"];
		$ilCtrl->setParameter($this, "user_id", $user_id);

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->__appendUserInfo($info, $user_id);
		// $this->__appendLPDetails($info,$this->details_obj_id,$user_id);

		include_once("./Services/Tracking/classes/class.ilTrUserObjectsPropsTableGUI.php");
		$table = new ilTrUserObjectsPropsTableGUI($this, "userDetails", "truop".$user_id,
			$user_id, $this->details_obj_id, $this->details_id);
		$this->tpl->setContent($info->getHTML().$table->getHTML());
	}

	function show()
	{
		// Clear table offset
		$this->ctrl->saveParameter($this,'offset',0);

		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case LP_MODE_REPOSITORY:
				$this->__initDetails($this->getRefId());
				$this->details();
				return true;
		}

		$this->__listObjects();
	}

	function __listObjects()
	{
		global $ilUser,$ilObjDataCache;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');

		/*
		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createList'),$this->lng->txt('pdf_export'));
		}
		 */

		/*
		// Limit info
		if($this->filter->limitReached())
		{
			$info = sprintf($this->lng->txt('trac_filter_limit_reached'),$this->filter->getLimit());
			$this->tpl->setVariable("LIMIT_REACHED",$info);
		}
		 */

		include_once("./Services/Tracking/classes/class.ilLPProgressTableGUI.php");
		$lp_table = new ilLPObjectsTableGUI($this, "");
		
		$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
	}

	function __initDetails($a_details_id)
	{
		global $ilObjDataCache;

		if(!$a_details_id)
		{
			$a_details_id = $this->getRefId();
		}
		if($a_details_id)
		{
			$_GET['details_id'] = $a_details_id;
			$this->details_id = $a_details_id;
			$this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
			$this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
			$this->details_mode = ilLPObjSettings::_lookupMode($this->details_obj_id);
		}
	}

	/**
	 * Show object-baes summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl;

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId());
		$tpl->setContent($table->getHTML());
	}
}
?>