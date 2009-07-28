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
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilLPFilterGUI, ilUserFilterGUI, ilPDFPresentation
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
include_once 'Services/Search/classes/class.ilUserFilterGUI.php';

class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		global $ilUser,$ilObjDataCache;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
		$this->__checkPDF();

		$this->__initFilterGUI();

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
		global $ilBench,$ilUser;

		$ilBench->start('LearningProgress','1000_LPListOfObjects');

		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'illpfiltergui':
				$this->ctrl->forwardCommand($this->filter_gui);
				break;

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

			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}

		$ilBench->stop('LearningProgress','1000_LPListOfObjects');
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

	function __renderContainerRow($a_parent_id,$a_item_id,$a_usr_id,$type,$level)
	{
		global $ilObjDataCache,$ilUser;

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

		$item_list =& ilLPItemListFactory::_getInstanceByRefId($a_parent_id,$a_item_id,$type);
		if($this->has_timings)
		{
			$item_list->readTimings();
			$item_list->enable('timings');
		}
		$item_list->setCurrentUser($a_usr_id);
		$item_list->readUserInfo();
		$item_list->setIndentLevel($level);

		// Mark
		$this->obj_tpl->setVariable("MARK",$item_list->getMark());

		// Edit link, details
		if($type != 'sahs_item' and
		   $type != 'objective' and
		   $type != 'event' and 
		   !$item_list->isAnonymized())
		{

			// Edit link
			$this->obj_tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameter($this,'details_id',$this->details_id);
			$this->ctrl->setParameter($this,"user_id",$a_usr_id);
			$this->ctrl->setParameter($this,'item_id',$a_item_id);
			$this->obj_tpl->setVariable('HREF_COMMAND',$this->ctrl->getLinkTarget($this,'editUser'));
			$this->obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('edit'));
			$this->obj_tpl->parseCurrentBlock();

			// Show checkbox and details button
			if(ilLPObjSettings::_isContainer($item_list->getMode()))
			{
				$item_list->addCheckbox(array('user_item_ids[]',
											  $a_usr_id.'_'.$a_item_id,
											  $this->__detailsShown($a_usr_id,$a_item_id)));
				$this->obj_tpl->setCurrentBlock("item_command");
				$this->ctrl->setParameter($this,'details_id',$this->details_id);
				$this->ctrl->setParameter($this,'user_item_ids',$a_usr_id.'_'.$a_item_id);
				if($this->__detailsShown($a_usr_id,$a_item_id))
				{
					$this->obj_tpl->setVariable('HREF_COMMAND',$this->ctrl->getLinkTarget($this,'hideDetails'));
					$this->obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('hide_details'));
				}
				else
				{
					$this->obj_tpl->setVariable('HREF_COMMAND',$this->ctrl->getLinkTarget($this,'showDetails'));
					$this->obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('show_details'));
				}
				$this->ctrl->clearParameters($this);
				$this->obj_tpl->parseCurrentBlock();
			}

		}

		if($this->has_timings)
		{
			if(ilTimingCache::_showWarning($a_item_id,$a_usr_id)) 
			{
				$this->obj_tpl->setCurrentBlock('warning_img');
				$this->obj_tpl->setVariable('WARNING_IMG',ilUtil::getImagePath('warning.gif'));
				$this->obj_tpl->setVariable('WARNING_ALT',$this->lng->txt('trac_editing_time_passed'));
				$this->obj_tpl->parseCurrentBlock();
			}

			$this->obj_tpl->setCurrentBlock('timing');
			$this->obj_tpl->setVariable('END_EDITING_TIME',$item_list->getEditingTime() ? 
									ilDatePresentation::formatDate(new ilDateTime($item_list->getEditingTime(),IL_CAL_UNIX)) :
									'');
			$this->obj_tpl->parseCurrentBlock();
		}
		
		// Status image
		$this->obj_tpl->setCurrentBlock("container_standard_row");

		$item_list->renderObjectDetails();

		$this->obj_tpl->setVariable("ITEM_HTML",$item_list->getHTML());
		if(!$item_list->isAnonymized())
		{
			$this->__showImageByStatus($this->obj_tpl,$item_list->getUserStatus());
		}
		$this->obj_tpl->setVariable("TBLROW",ilUtil::switchColor($this->container_row_counter,'tblrow1','tblrow2'));
		$this->obj_tpl->parseCurrentBlock();

		if(!$this->__detailsShown($a_usr_id,$a_item_id))
		{
			return true;
		}
		if($type == 'sahs_item' or
		   $type == 'objective' or
		   $type == 'event')
		{
			return true;
		}
		
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		foreach(ilLPCollectionCache::_getItems($ilObjDataCache->lookupObjId($a_item_id)) as $child_id)
		{
			switch($item_list->getMode())
			{
				case LP_MODE_OBJECTIVES:
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,'objective',$level + 2);
					break;

				case LP_MODE_SCORM:
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,'sahs_item',$level + 2);
					break;

				default:
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,
												$ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($child_id)),$level + 2);
					break;
			}
		}
	}


	function details()
	{
		global $ilObjDataCache;
		global $ilBench;

		$ilBench->start('LearningProgress','1200_LPListOfObjects_details');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		// Show back button
		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP or
		   $this->getMode() == LP_MODE_ADMINISTRATION)
		{
			$this->__showButton($this->ctrl->getLinkTarget($this,'show'),$this->lng->txt('trac_view_list'));
		}

		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createDetails'),$this->lng->txt('pdf_export'));
		}

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
		$item_list = & ilLPItemListFactory::_getInstanceByRefId(0,$this->details_id,$this->details_type);
		$info =& $item_list->renderObjectInfo();
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());

		$this->__showUsersList();
		$ilBench->stop('LearningProgress','1200_LPListOfObjects_details');
	}



	function __showUsersList()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

		global $ilObjDataCache,$ilUser;

		$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->details_obj_id);
		$in_progress = ilLPStatusWrapper::_getInProgress($this->details_obj_id);
		$completed = ilLPStatusWrapper::_getCompleted($this->details_obj_id);
		$failed = ilLPStatusWrapper::_getFailed($this->details_obj_id);
		$all_users = array_merge($completed,$in_progress,$not_attempted,$failed);

		if(count($all_users) > 1)
		{
			include_once 'Services/Search/classes/class.ilUserFilterGUI.php';
			$user_filter_gui = new ilUserFilterGUI($ilUser->getId());
			$this->tpl->setVariable("FILTER",$user_filter_gui->getHTML());
			$user_filter = new ilUserFilter($ilUser->getId());
			$user_filter->enableMemberFilter(true);
			$user_filter->setPossibleUsers($all_users);
			
			$all_users = $user_filter->getUsers();
			if(!count($all_users))
			{
				ilUtil::sendFailure($this->lng->txt('trac_filter_no_access'));
				return false;
			}
		}

		$all_users = $this->__sort($all_users,'usr_data','lastname','usr_id');
		$sliced_users = array_slice($all_users,$this->offset,$this->max_count);
		$this->obj_tpl = new ilTemplate('tpl.lp_loo_user_list.html',true,true,'Services/Tracking');


		$this->__initFilter();
		$type = $this->filter->getFilterType();
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

		// Show linkbar
		if(count($all_users) > $this->max_count)
		{
			$this->obj_tpl->setCurrentBlock("lp_linkbar_block");
			$this->ctrl->setParameter($this,'details_id',$this->details_id);
			$this->obj_tpl->setVariable("LP_LINKBAR",ilUtil::Linkbar($this->ctrl->getLinkTarget($this,'details'),
																  count($all_users),
																  $this->max_count,
																  (int) $this->offset,
																  array(),
																  array('link' => '',
																		'prev' => '<<<',
																		'next' => '>>>')));
			$this->obj_tpl->parseCurrentBlock();
		}
		// Render item list
		$this->container_row_counter = 0;
		foreach($sliced_users as $user)
		{
			$this->__renderContainerRow(0,$this->details_id,$user,'usr',0);
			$this->container_row_counter++;
		}

		// Hide button
		$this->obj_tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$this->obj_tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('show_details'));
		$this->obj_tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setVariable("LP_OBJECTS",$this->obj_tpl->get());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
	}

	function showDetails()
	{
		if(isset($_GET['user_item_ids']))
		{
			$ids = array($_GET['user_item_ids']);
		}
		else
		{
			unset($_SESSION['lp_show'][$this->details_id]);
			$ids = $_POST['user_item_ids'] ? $_POST['user_item_ids'] : array();
		}
		foreach($ids as $id)
		{			
			$_SESSION['lp_show'][$this->details_id][$id] = true;
		}
		$this->details();

		return true;
	}

	function hideDetails()
	{
		if(isset($_GET['user_item_ids']))
		{
			unset($_SESSION['lp_show'][$this->details_id]["$_GET[user_item_ids]"]);
			$this->details();
			return true;
		}
	}

	function __detailsShown($a_usr_id,$item_id)
	{
		return $_SESSION['lp_show'][$this->details_id][$a_usr_id.'_'.$item_id] ? true : false;
		#return $_SESSION['lp_show'][$this->details_id][$a_usr_id];
	}


	function show()
	{
		global $ilObjDataCache;
		global $ilBench;

		$ilBench->start('LearningProgress','1100_LPListOfObjects_show');

		// Clear table offset
		$this->ctrl->saveParameter($this,'offset',0);

		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case LP_MODE_REPOSITORY:
				$this->__initDetails($this->getRefId());
				$this->details();
				
				$ilBench->stop('LearningProgress','1100_LPListOfObjects_show');
				return true;
		}
		$this->__listObjects();
		$ilBench->stop('LearningProgress','1100_LPListOfObjects_show');
		return true;
	}

	function __listObjects()
	{
		global $ilUser,$ilObjDataCache;

		include_once './Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		$this->__initFilter();
		$this->__showFilter();

		$tpl = new ilTemplate('tpl.lp_loo_objects.html',true,true,'Services/Tracking');

		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createList'),$this->lng->txt('pdf_export'));
		}

		$this->filter->setRequiredPermission('edit_learning_progress');
		if(!count($objs = $this->filter->getObjects()))
		{
			ilUtil::sendFailure($this->lng->txt('trac_filter_no_access'));
			return true;
		}

		// Limit info
		if($this->filter->limitReached())
		{
			$info = sprintf($this->lng->txt('trac_filter_limit_reached'),$this->filter->getLimit());
			$this->tpl->setVariable("LIMIT_REACHED",$info);
		}

		// Show table header
		$tpl->setVariable("HEAD_STATUS",$this->lng->txt('trac_status'));
		$tpl->setVariable("HEAD_OPTIONS",$this->lng->txt('actions'));
		
		$type = $this->filter->getFilterType();
		$tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_'.$type.'.gif'));
		$tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_'.$type));
		$tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('objs_'.$type));

		// Sort objects by title
		$sorted_objs = $this->__sort(array_keys($objs),'object_data','title','obj_id');

		// Render item list
		$counter = 0;
		foreach($sorted_objs as $object_id)
		{
			$item_list =& ilLPItemListFactory::_getInstance(0,$object_id,$ilObjDataCache->lookupType($object_id));
			$item_list->read();
			$item_list->addCheckbox(array('item_id[]',$object_id,false));
			$item_list->setCmdClass(get_class($this));
			$item_list->addReferences($objs[$object_id]['ref_ids']);
			$item_list->enable('path');
			$item_list->renderObjectList();

			// Details link
			#if(!$this->isAnonymized())
			#{
			#	$tpl->setCurrentBlock("item_command");
			#	$this->ctrl->setParameter($this,'details_id',$object_id);
			#	$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTarget($this,'details'));
			#	$tpl->setVariable("TXT_COMMAND",$this->lng->txt('details'));
			#	$tpl->parseCurrentBlock();
			#}
			
			// Hide link
			$tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameterByClass('illpfiltergui','hide',$object_id);
			$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTargetByClass('illpfiltergui','hide'));
			$tpl->setVariable("TXT_COMMAND",$this->lng->txt('trac_hide'));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("container_standard_row");
			$tpl->setVariable("ITEM_HTML",$item_list->getHTML());
			$tpl->setVariable("TBLROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$tpl->parseCurrentBlock();
		}

		// Hide button
		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('trac_hide'));
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass('illpfiltergui'));

		$this->tpl->setVariable("LP_OBJECTS",$tpl->get());
	}

	// Private
	function __showFilter()
	{
		global $ilBench;

		$ilBench->start('LearningProgress','1110_LPListOfObjects_showFilter');
		$this->tpl->setVariable("FILTER",$this->filter_gui->getHTML());
		$ilBench->stop('LearningProgress','1110_LPListOfObjects_showFilter');
	}

	function __initFilterGUI()
	{
		global $ilUser;

		include_once './Services/Tracking/classes/class.ilLPFilterGUI.php';

		$this->filter_gui = new ilLPFilterGUI($ilUser->getId());
	}

	function __initFilter()
	{
		global $ilUser;

		include_once './Services/Tracking/classes/class.ilLPFilter.php';

		$this->filter = new ilLPFilter($ilUser->getId());
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