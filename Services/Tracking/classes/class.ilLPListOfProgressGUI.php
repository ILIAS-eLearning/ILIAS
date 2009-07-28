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
   * Class ilLPListOfProgress
   *
   * @author Stefan Meyer <smeyer@databay.de>
   *
   * @version $Id$
   *
   * @ilCtrl_Calls ilLPListOfProgressGUI: ilLPFilterGUI, ilPDFPresentation, ilLPProgressTableGUI
   *
   * @package ilias-tracking
   *
   */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';

class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
	var $tracked_user = null;
	var $show_user_info = false;
	var $filter_gui = null;

	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfProgressGUI($a_mode,$a_ref_id,$a_user_id = 0)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id,$a_user_id);
		$this->__checkPDF();

		$this->__initFilterGUI();
		$this->__initUser($a_user_id);
		
		// Set item id for details
		$this->__initDetails((int) $_GET['details_id']);
		$this->ctrl->saveParameter($this,'details_id',$_REQUEST['details_id']);
	}
		

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "show");
		$this->ctrl->saveParameter($this,'user_id',$this->getUserId());
		switch($this->ctrl->getNextClass())
		{
			case 'illpfiltergui':
				$this->ctrl->forwardCommand($this->filter_gui);
				break;

			case 'ilpdfpresentation':
				include_once './Services/Tracking/classes/class.ilPDFPresentation.php';
				$pdf_gui = new ilPDFPresentation($this->getMode(),$this->details_id,$this->getUserId(),$this->tracked_user->getId());
				$pdf_gui->setType(LP_ACTIVE_PROGRESS);
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($pdf_gui);
				break;

			case 'illpprogresstablegui':
				include_once("./Services/Tracking/classes/class.ilLPProgressTableGUI.php");
				$lp_table = new ilLPProgressTableGUI($this, "");
				$lp_table->initFilter();
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($lp_table);
				break;
				
			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		global $ilObjDataCache;

		switch($this->getMode())
		{
			// Show only detail of current repository item if called from repository
			case LP_MODE_REPOSITORY:
				$this->__initDetails($this->getRefId());
				return $this->details();

			case LP_MODE_USER_FOLDER:
				// if called from user folder obj_id is id of current user
				$this->__initUser($this->getUserId());
				break;
		}

		// not called from repository
 		$this->__showProgressList();
	}

	function details()
	{
		// Show back button to crs if called from crs. Otherwise if called from personal desktop or administration
		// show back to list
		if((int) $_GET['crs_id'])
		{
			$this->ctrl->setParameter($this,'details_id',(int) $_GET['crs_id']);
			$this->__showButton($this->ctrl->getLinkTarget($this,'details'),$this->lng->txt('trac_view_crs'));
		}
		elseif($this->getMode() == LP_MODE_PERSONAL_DESKTOP or
			   $this->getMode() == LP_MODE_ADMINISTRATION or
			   $this->getMode() == LP_MODE_USER_FOLDER)
		{
			$this->__showButton($this->ctrl->getLinkTarget($this,'show'),$this->lng->txt('trac_view_list'));
		}

		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createDetails'),$this->lng->txt('pdf_export'));
		}


		if(!ilLPObjSettings::_isContainer($this->details_mode))
		{
			return $this->__showDetails();
		}
		else
		{
			return $this->__showContainerList();
		}
	}

	function __showDetails()
	{
		global $ilObjDataCache,$ilCtrl;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_lm_details.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($ilCtrl->getFormAction($this));

		$this->__appendUserInfo($info);
		$this->__showObjectDetails($info,$this->details_obj_id);
		$this->__appendLPDetails($info,$this->details_obj_id,$this->tracked_user->getId());
	
		// Finally set template variable
		$this->tpl->setVariable("LM_INFO",$info->getHTML());
	}

	function __showContainerList()
	{
		global $ilObjDataCache,$ilCtrl;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_progress_container.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($ilCtrl->getFormAction($this));
		

		$this->__appendUserInfo($info);
		$this->__showObjectDetails($info,$this->details_obj_id);
		$this->__appendLPDetails($info,$this->details_obj_id,$this->tracked_user->getId());
		
		// Finally set template variable
		$this->tpl->setVariable("LM_INFO",$info->getHTML());

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_'.$this->details_type.'.gif'));
		$this->tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_'.$this->details_type));
		$this->tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('trac_'.$this->details_type.'_relevant_items'));

		// Show table header
		$this->tpl->setVariable("HEAD_STATUS",$this->lng->txt('trac_status'));
		$this->tpl->setVariable("HEAD_OPTIONS",$this->lng->txt('actions'));

		// Show timings header
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		if($this->has_timings = ilCourseItems::_hasCollectionTimings($this->details_id))
		{
			$this->tpl->setVariable('HEAD_TIMING',$this->lng->txt('trac_head_timing'));
			$this->tpl->setVariable('HEAD_TIME_PASSED',$this->lng->txt('trac_time_passed'));
		}
		$this->container_row_counter = 0;

		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		foreach(ilLPCollectionCache::_getItems($this->details_obj_id) as $item_id)
		{
			switch($this->details_mode)
			{
				case LP_MODE_SCORM:
					$type = 'sahs_item';
					break;
				case LP_MODE_OBJECTIVES:
					$type = 'objective';
					break;
				default:
					$type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($item_id));
					break;
			}
			$this->__renderContainerRow($this->details_id,$item_id,$type,0);
		}
		$this->tpl->setVariable("LEGEND",$this->__getLegendHTML());
	}

	function __renderContainerRow($parent_id,$item_id,$type,$level)
	{
		global $ilObjDataCache,$ilUser,$ilAccess;

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
		include_once 'Modules/Course/classes/Timings/class.ilTimingCache.php';

		$item_list =& ilLPItemListFactory::_getInstanceByRefId($parent_id,$item_id,$type);
		if($this->has_timings)
		{
			$item_list->readTimings();
			$item_list->enable('timings');
		}
		
		$item_list->setCurrentUser($this->tracked_user->getId());
		$item_list->readUserInfo();
		$item_list->setIndentLevel($level);
		$item_list->renderContainerProgress();


		// Details link
		if($type != 'sahs_item' and
		   $type != 'objective')
		{
			if(!$ilAccess->checkAccess('read','',$item_id))
			{
				return false;
			}
			if(ilLPObjSettings::_isContainer($item_list->getMode()))
			{
				$this->tpl->setCurrentBlock("item_command");
				$this->ctrl->setParameter($this,'details_id',$this->details_id);
				$this->ctrl->setParameter($this,'item_id',$item_id);
				if($this->__detailsShown($item_id))
				{
					$this->tpl->setVariable('HREF_COMMAND',$this->ctrl->getLinkTarget($this,'hideDetails'));
					$this->tpl->setVariable("TXT_COMMAND",$this->lng->txt('hide_details'));
				}
				else
				{
					$this->tpl->setVariable('HREF_COMMAND',$this->ctrl->getLinkTarget($this,'showDetails'));
					$this->tpl->setVariable("TXT_COMMAND",$this->lng->txt('show_details'));
				}
				$this->tpl->parseCurrentBlock();
			}
			#$this->tpl->setCurrentBlock("item_command");
			#$this->ctrl->setParameter($this,'details_id',$item_id);
			#$this->ctrl->setParameter($this,'crs_id',$this->details_id);
			#$this->tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTarget($this,'details'));
			#$this->tpl->setVariable("TXT_COMMAND",$this->lng->txt('details'));
			#$this->tpl->parseCurrentBlock();

			if($this->has_timings and ilTimingCache::_showWarning($item_id,$this->tracked_user->getId()))
			{
				$this->tpl->setCurrentBlock('warning_img');
				$this->tpl->setVariable('WARNING_IMG',ilUtil::getImagePath('warning.gif'));
				$this->tpl->setVariable('WARNING_ALT',$this->lng->txt('trac_editing_time_passed'));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if($this->has_timings)
		{
			$this->tpl->setCurrentBlock('timing');
			$this->tpl->setVariable('END_EDITING_TIME',$item_list->getEditingTime() ? 
									ilDatePresentation::formatDate(new ilDateTime($item_list->getEditingTime(),IL_CAL_UNIX)) :
									'');
			$this->tpl->parseCurrentBlock();
		}

		// Status image
		$this->tpl->setCurrentBlock("container_standard_row");
		$this->tpl->setVariable("ITEM_HTML",$item_list->getHTML());
		$this->__showImageByStatus($this->tpl,$item_list->getUserStatus());
		$this->tpl->setVariable("TBLROW",ilUtil::switchColor($this->container_row_counter++,'tblrow1','tblrow2'));
		$this->tpl->parseCurrentBlock();


		if($type == 'sahs_item' or
		   $type == 'objective')
		{
			return true;
		}

		if(!$this->__detailsShown($item_id))
		{
			return true;
		}

		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		foreach(ilLPCollectionCache::_getItems($ilObjDataCache->lookupObjId($item_id)) as $child_id)
		{
			switch($item_list->getMode())
			{
				case LP_MODE_OBJECTIVES:
					$this->__renderContainerRow($child_id,'objective',$level + 1);
					break;

				case LP_MODE_SCORM:
					$this->__renderContainerRow($item_id,$child_id,'sahs_item',$level + 1);
					break;

				default:
					$this->__renderContainerRow($item_id,
												$child_id,
												$ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($child_id)),$level + 1);
					break;
			}
		}
	}

	function __appendUserInfo(&$info)
	{
		if($this->show_user_info)
		{
			$info->addSection($this->lng->txt("trac_user_data"));
			$info->addProperty($this->lng->txt('username'),$this->tracked_user->getLogin());
			$info->addProperty($this->lng->txt('name'),$this->tracked_user->getFullname());
			$info->addProperty($this->lng->txt('last_login'),
				ilDatePresentation::formatDate(new ilDateTime($this->tracked_user->getLastLogin(),IL_CAL_DATETIME)));
			$info->addProperty($this->lng->txt('trac_total_online'),
							   ilFormat::_secondsToString(ilOnlineTracking::_getOnlineTime($this->tracked_user->getId())));
			return true;
		}
		return false;
	}

	function __showFilter()
	{
		$this->tpl->setVariable("FILTER",$this->filter_gui->getHTML());
	}

	function __showProgressList()
	{
		global $ilUser,$ilObjDataCache,$ilCtrl;

		include_once './Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_progress.html','Services/Tracking');
//		$this->__showFilter();

		// User info
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($ilCtrl->getFormAction($this));
		
		if ($this->__appendUserInfo($info))
		{
			$this->tpl->setVariable("USER_INFO",$info->getHTML());
		}

		if($this->activePDF())
		{
			$this->__showButton($this->ctrl->getLinkTargetByClass('ilpdfpresentation','createList'),$this->lng->txt('pdf_export'));
		}

		$this->__initFilter();
		$tpl = new ilTemplate('tpl.lp_progress_list.html',true,true,'Services/Tracking');
		$this->filter->setRequiredPermission('read');
		if(!count($objs = $this->filter->getObjects()))
		{
			ilUtil::sendFailure($this->lng->txt('trac_filter_no_access'));
//			return true;
		}

		// Output filter limit info
		if($this->filter->limitReached())
		{
			$info = sprintf($this->lng->txt('trac_filter_limit_reached'),$this->filter->getLimit());
			$tpl->setVariable("LIMIT_REACHED",$info);
		}

		$type = $this->filter->getFilterType();
		
/*		$tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_'.$type.'.gif'));
		$tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_'.$type));
		$tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('objs_'.$type));

		// Show table header
		$tpl->setVariable("HEAD_STATUS",$this->lng->txt('trac_status'));
		$tpl->setVariable("HEAD_OPTIONS",$this->lng->txt('actions'));
*/
		// Sort objects by title
		$sorted_objs = $this->__sort(array_keys($objs),'object_data','title','obj_id');
		// Render item list
		$counter = 0;
/*		foreach($sorted_objs as $object_id)
		{
			$item_list =& ilLPItemListFactory::_getInstance(0,$object_id,$ilObjDataCache->lookupType($object_id));
			$item_list->setCurrentUser($this->tracked_user->getId());
			$item_list->readUserInfo();
			$item_list->addCheckbox(array('item_id[]',$object_id,false));
			$item_list->setCmdClass(get_class($this));
			$item_list->addReferences($objs[$object_id]['ref_ids']);
			$item_list->enable('path');
			$item_list->renderSimpleProgress();

			// Details link
			#$tpl->setCurrentBlock("item_command");
			#$this->ctrl->setParameter($this,'details_id',$object_id);
			#$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTarget($this,'details'));
			#$tpl->setVariable("TXT_COMMAND",$this->lng->txt('details'));
			#$tpl->parseCurrentBlock();

			
			// Hide link
			$tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameterByClass('illpfiltergui','hide',$object_id);
			$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTargetByClass('illpfiltergui','hide'));
			$tpl->setVariable("TXT_COMMAND",$this->lng->txt('trac_hide'));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("container_standard_row");
			$tpl->setVariable("ITEM_HTML",$item_list->getHTML());
			$this->__showImageByStatus($tpl,$item_list->getUserStatus());
			$tpl->setVariable("TBLROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$tpl->parseCurrentBlock();
		}*/
		
// new table
reset($sorted_objs);
include_once("./Services/Tracking/classes/class.ilLPProgressTableGUI.php");
$lp_table = new ilLPProgressTableGUI($this, "", $type, $this->tracked_user, $sorted_objs);
$lp_table->setData($sorted_objs);
//$tpl->setVariable("LP_TABLE", $lp_table->getHTML());
$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());

		// Hide button
/*		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('trac_hide'));
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass('illpfiltergui'));
*/
//		$this->tpl->setVariable("LP_OBJECTS",$tpl->get());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
	}
		


	function __initUser($a_usr_id = 0)
	{
		global $ilUser,$rbacreview;

		if($_POST['user_id'])
		{
			$a_usr_id = $_POST['user_id'];
			$this->ctrl->setParameter($this,'user_id',$_POST['user_id']);
		}

		if($a_usr_id)
		{
			$this->tracked_user = ilObjectFactory::getInstanceByObjId($a_usr_id);
		}
		else
		{
			$this->tracked_user = $ilUser;
		}
		
		// Check access
		if(!$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID))
		{
			$this->tracked_user = $ilUser;
		}
		
		$this->show_user_info = ($this->tracked_user->getId() != $ilUser->getId());
		return true;
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
		if($this->tracked_user->getId() != $ilUser->getId())
		{
			$this->filter->toggleAnonymizedCheck(true);
		}
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
			$ref_ids = ilObject::_getAllReferences($a_details_id);
			
			$this->details_id = $a_details_id;
			$this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
			$this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
			$this->details_mode = ilLPObjSettings::_lookupMode($this->details_obj_id);
		}
	}


	function showDetails()
	{
		if(isset($_GET['item_id']))
		{
			$ids = array($_GET['item_id']);
		}
		else
		{
			unset($_SESSION['lp_show_progress'][$this->details_id]);
			$ids = $_POST['item_id'] ? $_POST['item_id'] : array();
		}
		foreach($ids as $id)
		{			
			$_SESSION['lp_show_progress'][$this->details_id][$id] = true;
		}
		$this->details();

		return true;
	}

	function hideDetails()
	{
		if(isset($_GET['item_id']))
		{
			unset($_SESSION['lp_show_progress'][$this->details_id]["$_GET[item_id]"]);
			$this->details();
			return true;
		}
	}


	function __detailsShown($item_id)
	{
		return $_SESSION['lp_show_progress'][$this->details_id][$item_id] ? true : false;
	}


}
?>