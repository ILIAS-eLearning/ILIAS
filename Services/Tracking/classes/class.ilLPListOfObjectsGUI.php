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
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilPDFPresentation, ilLPObjectsTableGUI
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

		$this->item_ref_id = (int) $_REQUEST['item_id'];
		$this->item_id = $ilObjDataCache->lookupObjId($this->item_ref_id);
		$this->offset = (int) $_GET['offset'];
		$this->ctrl->saveParameter($this,'offset',$this->offset);
		$this->ctrl->saveParameter($this,'details_id',$_REQUEST['details_id']);
		$this->max_count = $ilUser->getPref('hits_per_page');
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
			case 'illpobjectstablegui':
				include_once './Services/Tracking/classes/class.ilLPObjectsTableGUI.php';
			    $table_gui = new ilLPObjectsTableGUI($this, "", $this->tracked_user);
				$this->ctrl->setReturn($this,'show');
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

			case 'iluserfiltergui':
				switch($this->getMode())
				{
					case LP_MODE_REPOSITORY:
						$this->ctrl->setReturn($this,'show');
						break;
					default:
						$this->ctrl->setReturn($this,'details');
						break;
				}
				$this->user_filter_gui = new ilUserFilterGUI($ilUser->getId());
				$this->ctrl->forwardCommand($this->user_filter_gui);
				break;

			case 'illpprogresstablegui':
				include_once("./Services/Tracking/classes/class.ilLPProgressTableGUI.php");
				$lp_table = new ilLPProgressTableGUI($this, "");
				$lp_table->initFilter();
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($lp_table);
				break;
			
			case 'illpobjectstablegui':
				include_once("./Services/Tracking/classes/class.ilLPObjectsTableGUI.php");
				$lp_table = new ilLPObjectsTableGUI($this, "");
				$lp_table->initFilter();
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($lp_table);
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
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		$marks = new ilLPMarks($this->item_id,$_REQUEST['user_id']);
		$marks->setMark(ilUtil::stripSlashes($_POST['mark']));
		$marks->setComment(ilUtil::stripSlashes($_POST['comment']));
		$marks->setCompleted((bool) $_POST['completed']);
		$marks->update();
		
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_updateStatus($this->item_id, $_REQUEST['user_id']);
		
		ilUtil::sendSuccess($this->lng->txt('trac_update_edit_user'));
		$this->details();
	}

	function editUser()
	{
		global $ilObjDataCache;

		// Load template
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_edit_user.html','Services/Tracking');

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
		$item_list = & ilLPItemListFactory::_getInstanceByRefId(0,$this->item_ref_id,$ilObjDataCache->lookupType($this->item_id));
		$info =& $item_list->renderObjectInfo();
		$this->__appendLPDetails($info,$this->item_id,(int) $_GET['user_id']);

		// Finally set template variable
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());

		$this->__showEditUser();
	}

	function __showEditUser()
	{
		global $ilObjDataCache;

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		$marks = new ilLPMarks($this->item_id,$_REQUEST['user_id']);

		$this->ctrl->setParameter($this,'user_id',(int) $_GET['user_id']);
		$this->ctrl->setParameter($this,'item_id',(int) $this->item_ref_id);
		$this->ctrl->setParameter($this,'details_id',$this->details_id);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TYPE_IMG",ilObjUser::_getPersonalPicturePath((int) $_GET['user_id'],'xxsmall'));
		$this->tpl->setVariable("ALT_IMG",$ilObjDataCache->lookupTitle((int) $_GET['user_id']));
		$this->tpl->setVariable("TXT_LP",$this->lng->txt('trac_learning_progress_tbl_header'));

		$this->tpl->setVariable("COMMENT",ilUtil::prepareFormOutput($marks->getComment(),false));

		$type = $ilObjDataCache->lookupType($this->item_id);
		if($type != 'lm')
		{
			$this->tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
			$this->tpl->setVariable("MARK",ilUtil::prepareFormOutput($marks->getMark(),false));
		}

		$this->tpl->setVariable("TXT_COMMENT",$this->lng->txt('trac_comment'));

		$mode = ilLPObjSettings::_lookupMode($this->item_id);
		if($mode == LP_MODE_MANUAL or $mode == LP_MODE_MANUAL_BY_TUTOR)
		{
			$completed = ilLPStatusWrapper::_getCompleted($this->item_id);
			
			$this->tpl->setVariable("mode_manual");
			$this->tpl->setVariable("TXT_COMPLETED",$this->lng->txt('trac_completed'));
			$this->tpl->setVariable("CHECK_COMPLETED",ilUtil::formCheckbox(in_array((int) $_GET['user_id'],$completed),
																		   'completed',
																		   '1'));
		}

		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));
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

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
		$item_list = & ilLPItemListFactory::_getInstanceByRefId(0,$this->details_id,$this->details_type);
		$info =& $item_list->renderObjectInfo();
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

		include_once "Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php";
		$gui = new ilTrObjectUsersPropsTableGUI($this, "details", "objectdetails".$this->details_obj_id, $this->details_obj_id);
		
		$this->tpl->setVariable("LP_OBJECTS", $gui->getHTML());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
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

		include_once './Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

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
}
?>