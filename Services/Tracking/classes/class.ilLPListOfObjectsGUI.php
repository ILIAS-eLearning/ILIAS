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
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		$this->__initFilterGUI();

		// Set item id for details
		$this->__initDetails((int) $_REQUEST['details_id']);

		$this->item_id = (int) $_REQUEST['item_id'];
		$this->offset = (int) $_GET['offset'];
		$this->ctrl->saveParameter($this,'offset',$this->offset);

		$this->max_count = 10;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
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

		$this->tpl->setVariable("MARK",ilUtil::prepareFormOutput($marks->getMark(),false));
		$this->tpl->setVariable("COMMENT",ilUtil::prepareFormOutput($marks->getComment(),false));


		$this->tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
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

	}

	function __showUserList()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		global $ilObjDataCache;

		$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->details_id);
		$in_progress = ilLPStatusWrapper::_getInProgress($this->details_id);
		$completed = ilLPStatusWrapper::_getCompleted($this->details_id);

		$all_users = $this->__sort(array_merge($completed,$in_progress,$not_attempted));
		
		$counter = 0;
		// Slice array
		$sliced_users = array_slice($all_users,$this->offset,$this->max_count);
		foreach($sliced_users as $user_id)
		{
			$cssrow = ilUtil::switchColor($counter++,'tblrow1','tblrow2');

			// Subitems for mode LP_MODE_COLLECTION
			if($this->details_mode == LP_MODE_COLLECTION)
			{
				foreach(ilLPCollections::_getItems($this->details_id) as $obj_id)
				{
					$this->tpl->setCurrentBlock("item_row");

					// show item_info
					$obj_tpl = new ilTemplate('tpl.lp_object.html',true,true,'Services/Tracking');
					$obj_tpl->setCurrentBlock("item_title");
					$obj_tpl->setVariable("TXT_TITLE",$ilObjDataCache->lookupTitle($obj_id));
					$obj_tpl->parseCurrentBlock();

					// Status
					if(in_array($user_id,ilLPStatusWrapper::_getInProgress($obj_id)))
					{
						$status = LP_STATUS_IN_PROGRESS;
					}
					elseif(in_array($user_id,ilLPStatusWrapper::_getCompleted($obj_id)))
					{
						$status = LP_STATUS_COMPLETED;
					}
					else
					{
						$status = LP_STATUS_NOT_ATTEMPTED;
					}
					$obj_tpl->setCurrentBlock("item_property");
					$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
					$obj_tpl->setVariable("VAL_PROP",$this->lng->txt($status));
					$obj_tpl->parseCurrentBlock();
			
					$obj_tpl->setCurrentBlock("item_properties");
					$obj_tpl->parseCurrentBlock();
					$this->tpl->setVariable("ITEM_INFO",$obj_tpl->get());

					$this->tpl->setVariable("ITEM_CSSROW",$cssrow);
					#$this->tpl->setVariable("ITEM_TDCSS",'option_value');
					$this->tpl->setVariable("ITEM_IMG",ilUtil::getImagePath('icon_'.$ilObjDataCache->lookupType($obj_id).'.gif'));
					$this->tpl->setVariable("ITEM_ALT",$this->lng->txt('obj_'.$ilObjDataCache->lookupType($obj_id)));

					$this->tpl->setVariable("ITEM_MARK",ilLPMarks::_lookupMark($user_id,$obj_id));
					$this->tpl->setVariable("ITEM_COMMENT",ilLPMarks::_lookupComment($user_id,$obj_id));
					

					$this->ctrl->setParameter($this,'user_id',$user_id);
					$this->ctrl->setParameter($this,"item_id",$obj_id);
					$this->ctrl->setParameter($this,'details_id',$this->details_id);
					$this->tpl->setVariable("ITEM_EDIT_COMMAND",$this->ctrl->getLinkTarget($this,'editUser'));
					$this->tpl->setVariable("ITEM_TXT_COMMAND",$this->lng->txt('edit'));
					$this->tpl->parseCurrentBlock();
				}			
			}

			$this->tpl->setCurrentBlock("user_row");

			// show user_info
			$user_tpl = new ilTemplate('tpl.lp_object.html',true,true,'Services/Tracking');
			$user_tpl->setCurrentBlock("item_title");
			$user_tpl->setVariable("TXT_TITLE",$ilObjDataCache->lookupTitle($user_id));
			$user_tpl->parseCurrentBlock();

			$user_tpl->setCurrentBlock("item_description");
			$user_tpl->setVariable("TXT_DESC",'['.ilObjUser::_lookupLogin($user_id).']');
			$user_tpl->parseCurrentBlock();

			// Status
			if(in_array($user_id,$in_progress))
			{
				$status = LP_STATUS_IN_PROGRESS;
			}
			elseif(in_array($user_id,$completed))
			{
				$status = LP_STATUS_COMPLETED;
			}
			else
			{
				$status = LP_STATUS_NOT_ATTEMPTED;
			}
			$user_tpl->setCurrentBlock("item_property");
			$user_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
			$user_tpl->setVariable("VAL_PROP",$this->lng->txt($status));
			$user_tpl->parseCurrentBlock();
			
			$user_tpl->setCurrentBlock("item_properties");
			$user_tpl->parseCurrentBlock();
			$this->tpl->setVariable("ROW_INFO",$user_tpl->get());

			$this->tpl->setVariable("CSSROW",$cssrow);
			$this->tpl->setVariable("TYPE_IMG",ilObjUser::_getPersonalPicturePath($user_id,'xxsmall'));
			$this->tpl->setVariable("TYPE_ALT",$ilObjDataCache->lookupTitle($user_id));
			$this->ctrl->setParameter($this,"user_id",$user_id);
			$this->ctrl->setParameter($this,'item_id',$this->details_id);
			$this->ctrl->setParameter($this,'details_id',$this->details_id);
			$this->tpl->setVariable("EDIT_COMMAND",$this->ctrl->getLinkTarget($this,'editUser'));
			$this->tpl->setVariable("TXT_COMMAND",$this->lng->txt('edit'));
			$this->tpl->setVariable("MARK",ilLPMarks::_lookupMark($user_id,$this->details_id));
			$this->tpl->setVariable("COMMENT",ilLPMarks::_lookupComment($user_id,$this->details_id));

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
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("HEADER_ALT",$this->lng->txt('trac_usr_list'));
		$this->tpl->setVariable("TXT_MARK",$this->lng->txt('trac_marks'));
		$this->tpl->setVariable("TXT_COMMENT",$this->lng->txt('trac_comments'));
		$this->tpl->setVariable("TXT_OPTIONS",$this->lng->txt('options'));
		
		return true;
	}
			



	function show()
	{
		global $ilObjDataCache;

		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case LP_MODE_REPOSITORY:
				$this->__initDetails($ilObjDataCache->lookupObjId($this->getRefId()));
				$this->details();
				return true;
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');
		$this->__showFilter();
		$this->__showItems();
	}

	// Private
	function __showFilter()
	{
		$this->tpl->setVariable("FILTER",$this->filter_gui->getHTML());
	}

	function __showItems()
	{
		$this->__initFilter();

		$tpl = new ilTemplate('tpl.lp_objects.html',true,true,'Services/Tracking');

		if(!count($objs = $this->filter->getObjects()))
		{
			sendInfo($this->lng->txt('trac_filter_no_access'));
			return true;
		}
		$type = $this->filter->getFilterType();
		$tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_'.$type.'.gif'));
		$tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_'.$type));
		$tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('objs_'.$type));

		$counter = 0;
		foreach($objs as $obj_id => $obj_data)
		{
			$tpl->touchBlock(ilUtil::switchColor($counter++,'row_type_1','row_type_2'));
			$tpl->setCurrentBlock("container_standard_row");
			$tpl->setVariable("ITEM_ID",$obj_id);

			$obj_tpl = new ilTemplate('tpl.lp_object.html',true,true,'Services/Tracking');
			$obj_tpl->setCurrentBlock("item_title");
			$obj_tpl->setVariable("TXT_TITLE",$obj_data['title']);
			$obj_tpl->parseCurrentBlock();

			if(strlen($obj_data['description']))
			{
				$obj_tpl->setCurrentBlock("item_description");
				$obj_tpl->setVariable("TXT_DESC",$obj_data['description']);
				$obj_tpl->parseCurrentBlock();
			}

			// detail link on if not anonymized
			if(!$this->isAnonymized())
			{
				$obj_tpl->setCurrentBlock("item_command");
				$this->ctrl->setParameter($this,'details_id',$obj_id);
				$obj_tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTarget($this,'details'));
				$obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('details'));
				$obj_tpl->parseCurrentBlock();
			}
			// hide link
			$obj_tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameterByClass('illpfiltergui','hide',$obj_id);
			$obj_tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTargetByClass('illpfiltergui','hide'));
			$obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('trac_hide'));
			$obj_tpl->parseCurrentBlock();

			$obj_tpl->setVariable("OCCURRENCES",$this->lng->txt('trac_occurrences'));
			foreach($obj_data['ref_ids'] as $ref_id)
			{
				$this->__insertPath($obj_tpl,$ref_id);
			}

			// Not attempted only for course collection
			if($not_attempted = ilLPStatusWrapper::_getCountNotAttempted($obj_id))
			{
				$obj_tpl->setCurrentBlock("item_property");
				$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_not_attempted'));
				$obj_tpl->setVariable("VAL_PROP",$not_attempted);
				$obj_tpl->parseCurrentBlock();
				$obj_tpl->touchBlock('newline_prop');
			}

			$obj_tpl->setCurrentBlock("item_property");
			$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_in_progress'));
			$obj_tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountInProgress($obj_id));
			$obj_tpl->parseCurrentBlock();

			$obj_tpl->touchBlock('newline_prop');
			$obj_tpl->setCurrentBlock("item_property");
			$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_completed'));
			$obj_tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountCompleted($obj_id));
			$obj_tpl->parseCurrentBlock();


			$obj_tpl->setCurrentBlock("item_properties");
			$obj_tpl->parseCurrentBlock();

			$tpl->setVariable("BLOCK_ROW_CONTENT",$obj_tpl->get());
			$tpl->parseCurrentBlock();
		}

		// Hide button
		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('trac_hide'));
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass('illpfiltergui'));

		$this->tpl->setVariable("LP_OBJECTS",$tpl->get());
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

	function __sort($a_user_ids)
	{
		global $ilDB;

		if(!$a_user_ids)
		{
			return array();
		}

		// use database to sort user array
		$where = "WHERE usr_id IN ('";
		$where .= implode("','",$a_user_ids);
		$where .= "') ";

		$query = "SELECT usr_id FROM usr_data ".
			$where.
			"ORDER BY login";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->usr_id;
		}

		return $user_ids ? $user_ids : array();
	}
}
?>