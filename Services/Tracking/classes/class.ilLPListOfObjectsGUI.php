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
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilLPFilterGUI
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		global $ilUser;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		$this->__initFilterGUI();

		// Set item id for details
		$this->__initDetails((int) $_REQUEST['details_id']);

		$this->item_id = (int) $_REQUEST['item_id'];
		$this->offset = (int) $_GET['offset'];
		$this->ctrl->saveParameter($this,'offset',$this->offset);

		$this->max_count = $ilUser->getPref('hits_per_page');
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilBench;

		$ilBench->start('LearningProgress','1000_LPListOfObjects');

		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'illpfiltergui':

				$this->ctrl->forwardCommand($this->filter_gui);
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
		sendInfo($this->lng->txt('trac_update_edit_user'));
		$this->details();
	}

	function editUser()
	{
		// Load template
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_edit_user.html','Services/Tracking');

		include_once("classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$this->__showObjectDetails($info,$this->item_id);
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
		$this->ctrl->setParameter($this,'item_id',(int) $this->item_id);
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

		if(ilLPObjSettings::_lookupMode($this->item_id) == LP_MODE_MANUAL)
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
		global $ilObjDataCache;

		global $ilBench;

		$ilBench->start('LearningProgress','1200_LPListOfObjects_details');


		// Load template
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_user_list.html','Services/Tracking');

		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP or
		   $this->getMode() == LP_MODE_ADMINISTRATION)
		{
			$this->__showButton($this->ctrl->getLinkTarget($this,'show'),$this->lng->txt('trac_view_list'));
		}

		include_once("classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$this->__showObjectDetails($info);

		// Finally set template variable
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
		$this->__showUserList();

		$ilBench->stop('LearningProgress','1200_LPListOfObjects_details');
	}

	function __showUserList()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		global $ilObjDataCache;

		$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->details_id);
		$in_progress = ilLPStatusWrapper::_getInProgress($this->details_id);
		$completed = ilLPStatusWrapper::_getCompleted($this->details_id);

		$all_users = $this->__sort(array_merge($completed,$in_progress,$not_attempted),'usr_data','lastname','usr_id');
		$sliced_users = array_slice($all_users,$this->offset,$this->max_count);

		$this->ctrl->setParameter($this,'details_id',$this->details_id);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("HEADER_ALT",$this->lng->txt('trac_usr_list'));
		$this->tpl->setVariable("TXT_STATUS",$this->lng->txt('trac_status'));
		$this->tpl->setVariable("TXT_OPTIONS",$this->lng->txt('actions'));


		if($this->details_type != 'lm')
		{
			$this->tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
		}

		if($this->details_mode == LP_MODE_COLLECTION)
		{
			$this->__readItemStatusInfo(array_merge($this->items = ilLPCollections::_getItems($this->details_id)),
										array($this->details_id));
		}
		else
		{
			$this->__readItemStatusInfo(array($this->details_id));
		}

		$counter = 0;
		foreach($sliced_users as $user_id)
		{
			$cssrow = ilUtil::switchColor($counter++,'tblrow1','tblrow2');

			// show user_info
			$this->tpl->setVariable("TXT_TITLE",$ilObjDataCache->lookupTitle($user_id));
			$this->tpl->setVariable("TXT_DESC",'['.ilObjUser::_lookupLogin($user_id).']');

			// Status
			$status = $this->__readStatus($this->details_id,$user_id);
			$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
			$this->tpl->setVariable("VAL_PROP",$this->lng->txt($status));

			$this->__showImageByStatus($this->tpl,$status);

			// Status info
			if($status_info = $this->__getStatusInfo($this->details_id,$user_id))
			{
				$this->tpl->setCurrentBlock("status_info");
				$this->tpl->setVariable("STATUS_PROP",$status_info[0]);
				$this->tpl->setVariable("STATUS_VAL",$status_info[1]);
				$this->tpl->parseCurrentBlock();
			}

			// Comment
			if(strlen($comment = ilLPMarks::_lookupComment($user_id,$this->details_id)))
			{
				$this->tpl->setCurrentBlock("comment_prop");
				$this->tpl->setVariable("TXT_PROP_COMM",$this->lng->txt('trac_comment'));
				$this->tpl->setVariable("VAL_PROP_COMM",$comment);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("CSSROW",$cssrow);
			$this->tpl->setVariable("TYPE_IMG",ilObjUser::_getPersonalPicturePath($user_id,'xxsmall'));
			$this->tpl->setVariable("TYPE_ALT",$ilObjDataCache->lookupTitle($user_id));
			$this->ctrl->setParameter($this,"user_id",$user_id);
			$this->ctrl->setParameter($this,'item_id',$this->details_id);

			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("EDIT_COMMAND",$this->ctrl->getLinkTarget($this,'editUser'));
			$this->tpl->setVariable("TXT_COMMAND",$this->lng->txt('edit'));
			$this->tpl->parseCurrentBlock();

			if($this->details_type != 'lm')
			{
				$this->tpl->setVariable("MARK_TDCSS",$cssrow);
				$this->tpl->setVariable("MARK",ilLPMarks::_lookupMark($user_id,$this->details_id));
			}
			
			// Details for course mode collection
			if($this->details_mode == LP_MODE_COLLECTION ||
				$this->details_mode == LP_MODE_SCORM)
			{
				// estimated processing time
				if ($this->details_mode == LP_MODE_COLLECTION)
				{
					$processing_time_info = $this->__readProcessingTime($user_id);
					if($this->tlt_sum and $processing_time_info['total_percent'] != "0.00%")
					{
						$this->tpl->setCurrentBlock("tlt_prop");
						$this->tpl->setVariable("TXT_PROP_TLT",$this->lng->txt('trac_processing_time'));
						$this->tpl->setVariable("VAL_PROP_TLT",$processing_time_info['total_percent']);
						$this->tpl->parseCurrentBlock();
					}
				}

				// user check
				$this->tpl->setVariable("CHECK_USER",ilUtil::formCheckbox((int) $this->__detailsShown($user_id),
																		  'user_ids[]',
																		  $user_id));
				
				$this->tpl->setCurrentBlock("cmd");
				$this->ctrl->setParameter($this,'details_user',$user_id);
				$this->tpl->setVariable("EDIT_COMMAND",$this->ctrl->getLinkTarget($this,$this->__detailsShown($user_id) ?
																				  'hideDetails' : 'showDetails'));
																				  
				$this->tpl->setVariable("TXT_COMMAND",$this->__detailsShown($user_id) ? 
										$this->lng->txt('hide_details') : $this->lng->txt('show_details'));
				$this->tpl->parseCurrentBlock();
			}				
			

			// show course details
			if($this->details_mode == LP_MODE_COLLECTION &&
				$this->__detailsShown($user_id))
			{
				foreach(ilLPCollections::_getItems($this->details_id) as $obj_id)
				{
					// show item_info
					$this->tpl->setVariable("ITEM_TITLE",$ilObjDataCache->lookupTitle($obj_id));

					// Status
					$status = $this->__readStatus($obj_id,$user_id);
					$this->tpl->setVariable("ITEM_PROP",$this->lng->txt('trac_status'));
					$this->tpl->setVariable("ITEM_VAL",$this->lng->txt($status));
					$this->__showImageByStatus($this->tpl,$status,'ITEM_');

					if($processing_time_info[$obj_id] and 
					   $processing_time_info[$obj_id] != "0.00%")

					{
						$this->tpl->setCurrentBlock("tlt_item_prop");
						$this->tpl->setVariable("TXT_PROP_ITEM_TLT",$this->lng->txt('trac_processing_time'));
						$this->tpl->setVariable("VAL_PROP_ITEM_TLT",$processing_time_info[$obj_id]);
						$this->tpl->parseCurrentBlock();
					}

					if(strlen($comment = ilLPMarks::_lookupComment($user_id,$obj_id)))
					{
						$this->tpl->setCurrentBlock("item_comment_prop");
						$this->tpl->setVariable("ITEM_TXT_PROP_COMM",$this->lng->txt('trac_comment'));
						$this->tpl->setVariable("ITEM_VAL_PROP_COMM",$comment);
						$this->tpl->parseCurrentBlock();
					}
					if($status_info = $this->__getStatusInfo($obj_id,$user_id))
					{
						$this->tpl->setCurrentBlock("item_status_info");
						$this->tpl->setVariable("ITEM_STATUS_PROP",$status_info[0]);
						$this->tpl->setVariable("ITEM_STATUS_VAL",$status_info[1]);
						$this->tpl->parseCurrentBlock();
					}
					
					$this->tpl->setCurrentBlock("item_image");
					$this->tpl->setVariable("ITEM_IMG",ilUtil::getImagePath('icon_'.$ilObjDataCache->lookupType($obj_id).'.gif'));
					$this->tpl->setVariable("ITEM_ALT",$this->lng->txt('obj_'.$ilObjDataCache->lookupType($obj_id)));
					$this->tpl->parseCurrentBlock();

					$this->ctrl->setParameter($this,'user_id',$user_id);
					$this->ctrl->setParameter($this,"item_id",$obj_id);
					$this->ctrl->setParameter($this,'details_id',$this->details_id);
					$this->tpl->setCurrentBlock("edit_command");
					$this->tpl->setVariable("ITEM_EDIT_COMMAND",$this->ctrl->getLinkTarget($this,'editUser'));
					$this->tpl->setVariable("ITEM_TXT_COMMAND",$this->lng->txt('edit'));
					$this->tpl->parseCurrentBlock();

					$this->tpl->setVariable("ITEM_CSSROW",$cssrow);
					$this->tpl->setVariable("ITEM_MARK",ilLPMarks::_lookupMark($user_id,$obj_id));

					$this->tpl->setCurrentBlock("item_row");
					$this->tpl->parseCurrentBlock();
				}			
			}

			// show scorm details 
			if($this->details_mode == LP_MODE_SCORM &&
				$this->__detailsShown($user_id))
			{
				include_once './content/classes/SCORM/class.ilSCORMItem.php';
				foreach(ilLPCollections::_getItems($this->details_id) as $item_id)
				{
					// show item_info
					$this->tpl->setVariable("ITEM_TITLE",ilSCORMItem::_lookupTitle($item_id));

					// Status
					//$status = $this->__readStatus($obj_id,$user_id);
					$status = $this->__readSCORMStatus($item_id,$user_id);
					$this->tpl->setVariable("ITEM_PROP",$this->lng->txt('trac_status'));
					$this->tpl->setVariable("ITEM_VAL",$this->lng->txt($status));
					$this->__showImageByStatus($this->tpl,$status,'ITEM_');
					

					$this->tpl->setVariable("ITEM_CSSROW", $cssrow);
					//$this->tpl->setVariable("ITEM_IMG",ilUtil::getImagePath('icon_'.$ilObjDataCache->lookupType($obj_id).'.gif'));
					//$this->tpl->setVariable("ITEM_ALT",$this->lng->txt('obj_'.$ilObjDataCache->lookupType($obj_id)));

					//$this->tpl->setVariable("ITEM_MARK",ilLPMarks::_lookupMark($user_id,$obj_id));

					/*
					$this->ctrl->setParameter($this,'user_id',$user_id);
					$this->ctrl->setParameter($this,"item_id",$obj_id);
					$this->ctrl->setParameter($this,'details_id',$this->details_id);
					$this->tpl->setVariable("ITEM_EDIT_COMMAND",$this->ctrl->getLinkTarget($this,'editUser'));
					$this->tpl->setVariable("ITEM_TXT_COMMAND",$this->lng->txt('edit'));
					*/

					$this->tpl->setCurrentBlock("item_row");
					$this->tpl->parseCurrentBlock();
				}			
			}

			$this->tpl->setCurrentBlock("user_row");
			$this->tpl->parseCurrentBlock();

				
		}
		// show commands
		if($this->details_mode == LP_MODE_COLLECTION ||
			$this->details_mode == LP_MODE_SCORM)
		{
			$this->tpl->setCurrentBlock("button_footer");
			$this->tpl->setVariable("FOOTER_CMD",'showDetails');
			$this->tpl->setVariable("FOOTER_CMD_TEXT",$this->lng->txt('show_details'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tblfooter");
			$this->tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
			$this->tpl->parseCurrentBlock();
		}

		// Show linkbar
		if(count($all_users) > $this->max_count)
		{
			$this->tpl->setCurrentBlock("linkbar");
			$this->ctrl->setParameter($this,'details_id',$this->details_id);
			$this->tpl->setVariable("LINKBAR",ilUtil::Linkbar($this->ctrl->getLinkTarget($this,'details'),
															  count($all_users),
															  $this->max_count,
															  (int) $this->offset,
															  array(),
															  array('link' => '',
																	'prev' => '<<<',
																	'next' => '>>>')));
			$this->tpl->parseCurrentBlock();
		}
		// no users found
		if(!count($all_users))
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("NO_CONTENT",$this->lng->txt('trac_no_content'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
		}
		
		return true;
	}

	function showDetails()
	{
		if(isset($_GET['details_user']))
		{
			$user_ids = array((int) $_GET['details_user']);
		}
		else
		{
			unset($_SESSION['lp_show'][$this->details_id]);
			$user_ids = $_POST['user_ids'] ? $_POST['user_ids'] : array();
		}
		foreach($user_ids as $user_id)
		{
			$_SESSION['lp_show'][$this->details_id][$user_id] = true;
		}
		$this->details();

		return true;
	}

	function hideDetails()
	{
		if(isset($_GET['details_user']))
		{
			unset($_SESSION['lp_show'][$this->details_id]["$_GET[details_user]"]);
			$this->details();
			return true;
		}
	}

	function __detailsShown($a_usr_id)
	{
		return $_SESSION['lp_show'][$this->details_id][$a_usr_id];
	}


	function show()
	{
		global $ilObjDataCache;

		global $ilBench;

		$ilBench->start('LearningProgress','1100_LPListOfObjects_show');


		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case LP_MODE_REPOSITORY:
				$this->__initDetails($ilObjDataCache->lookupObjId($this->getRefId()));
				$this->details();
				
				$ilBench->stop('LearningProgress','1100_LPListOfObjects_show');
				return true;
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');
		$this->__showFilter();
		$this->__showItems();
		$ilBench->stop('LearningProgress','1100_LPListOfObjects_show');
	}

	// Private
	function __readProcessingTime($a_usr_id)
	{
		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		if(!is_array($this->tlt))
		{
			$this->__readTLT();
		}

		$pt_info = array();
		foreach($this->tlt as $item_id => $info)
		{
			if(in_array($a_usr_id,ilLPStatusWrapper::_getCompleted($item_id)))
			{
				$pt_info['total'] += $info['tlt'];
				$pt_info[$item_id] = "100%";
				continue;
			}

			switch($this->obj_data[$item_id]['type'])
			{
				case 'lm':
			
					$lp_info = ilLearningProgress::_getProgress($a_usr_id,$item_id);
					$pt_info['total'] += min($lp_info['spent_time'],$info['tlt']);
					$pt_info[$item_id] = $this->__getPercent($info['tlt'],min($lp_info['spent_time'],$info['tlt']));
					break;
			}
		}

		$pt_info['total_percent'] = $this->__getPercent($this->tlt_sum,$pt_info['total']);

		return $pt_info ? $pt_info : array();
	}

	function __readTLT()
	{
		global $ilObjDataCache;

		include_once 'Services/MetaData/classes/class.ilMDEducational.php';

		$this->tlt_sum = 0;
		$this->tlt = array();
		foreach($this->items as $item_id)
		{
			if($tlt = ilMDEducational::_getTypicalLearningTimeSeconds($item_id))
			{
				$this->tlt[$item_id]['tlt'] = $tlt;
			}
			$this->tlt_sum += $this->tlt[$item_id]['tlt'];
		}
		return true;
	}
		



	function __showFilter()
	{
		global $ilBench;

		$ilBench->start('LearningProgress','1110_LPListOfObjects_showFilter');
		$this->tpl->setVariable("FILTER",$this->filter_gui->getHTML());
		$ilBench->stop('LearningProgress','1110_LPListOfObjects_showFilter');
	}

	function __showItems()
	{
		global $ilBench;

		$ilBench->start('LearningProgress','1120_LPListOfObjects_showItems');

		$this->__initFilter();

		$tpl = new ilTemplate('tpl.lp_objects.html',true,true,'Services/Tracking');

		
		$ilBench->start('LearningProgress','1121_LPListOfObjects_showItems_getObjects');
		if(!count($objs = $this->filter->getObjects()))
		{
			sendInfo($this->lng->txt('trac_filter_no_access'));
			return true;
		}
		$ilBench->stop('LearningProgress','1121_LPListOfObjects_showItems_getObjects');
		if($this->filter->limitReached())
		{
			$info = sprintf($this->lng->txt('trac_filter_limit_reached'),$this->filter->getLimit());
			$tpl->setVariable("LIMIT_REACHED",$info);
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
		$counter = 0;
		foreach($sorted_objs as $obj_id)
		{
			$obj_data =& $objs[$obj_id];

			$tpl->setVariable("TBLROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$tpl->setVariable("ITEM_ID",$obj_id);

			$obj_tpl = new ilTemplate('tpl.lp_object.html',true,true,'Services/Tracking');

			// Title/description
			$tpl->setVariable("TXT_TITLE",$obj_data['title']);

			if(strlen($obj_data['description']))
			{
				$tpl->setCurrentBlock("item_description");
				$tpl->setVariable("TXT_DESC",$obj_data['description']);
				$tpl->parseCurrentBlock();
			}

			// detail link on if not anonymized
			if(!$this->isAnonymized())
			{
				$tpl->setCurrentBlock("item_command");
				$this->ctrl->setParameter($this,'details_id',$obj_id);
				$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTarget($this,'details'));
				$tpl->setVariable("TXT_COMMAND",$this->lng->txt('details'));
				$tpl->parseCurrentBlock();
			}
			// hide link
			$tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameterByClass('illpfiltergui','hide',$obj_id);
			$tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTargetByClass('illpfiltergui','hide'));
			$tpl->setVariable("TXT_COMMAND",$this->lng->txt('trac_hide'));
			$tpl->parseCurrentBlock();

			$tpl->setVariable("OCCURRENCES",$this->lng->txt('trac_occurrences'));
			foreach($obj_data['ref_ids'] as $ref_id)
			{
				$this->__insertPath($tpl,$ref_id);
			}

			// Not attempted only for course collection
			if($not_attempted = ilLPStatusWrapper::_getCountNotAttempted($obj_id))
			{
				$tpl->setCurrentBlock("item_property");
				$tpl->setVariable("TXT_PROP",$this->lng->txt('trac_not_attempted'));
				$tpl->setVariable("VAL_PROP",$not_attempted);
				$tpl->parseCurrentBlock();
				$tpl->touchBlock('newline_prop');
			}

			$tpl->setCurrentBlock("item_property");
			$tpl->setVariable("TXT_PROP",$this->lng->txt('trac_in_progress'));
			$tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountInProgress($obj_id));
			$tpl->parseCurrentBlock();

			$tpl->touchBlock('newline_prop');
			$tpl->setCurrentBlock("item_property");
			$tpl->setVariable("TXT_PROP",$this->lng->txt('trac_completed'));
			$tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountCompleted($obj_id));
			$tpl->parseCurrentBlock();


			$tpl->setCurrentBlock("item_properties");
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("container_standard_row");
			$tpl->parseCurrentBlock();
		}

		// Hide button
		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('trac_hide'));
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass('illpfiltergui'));

		$this->tpl->setVariable("LP_OBJECTS",$tpl->get());

		$ilBench->stop('LearningProgress','1120_LPListOfObjects_showItems');
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


		if($a_details_id)
		{
			$this->details_id = $a_details_id;
			$this->details_type = $ilObjDataCache->lookupType($this->details_id);
			$this->details_mode = ilLPObjSettings::_lookupMode($this->details_id);
		}
	}
}
?>